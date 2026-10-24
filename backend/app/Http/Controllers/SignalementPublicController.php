<?php

namespace App\Http\Controllers;

use App\Models\Preuve;
use App\Models\Signalement;
use App\Services\ImageSanitizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SignalementPublicController extends Controller
{
    /**
     * Génère un code de suivi unique en utilisant random_int() (CSPRNG).
     * Évite str_shuffle() qui n'est pas cryptographiquement sûr.
     */
    private function generateCode(): string
    {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        do {
            $code = 'SG-' . $this->randomStr(4, $chars) . '-' . $this->randomStr(4, $chars);
        } while (Signalement::where('code', $code)->exists());
        return $code;
    }

    private function randomStr(int $length, string $chars): string
    {
        $result = '';
        $max    = strlen($chars) - 1;
        for ($i = 0; $i < $length; $i++) {
            $result .= $chars[random_int(0, $max)];
        }
        return $result;
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'categorie'     => 'required|in:Administration publique,Police et gendarmerie,Santé et hôpitaux,Éducation et universités,Douanes et frontières,Justice et tribunaux,Marchés publics,Autre',
            'description'   => 'required|string|min:50|max:10000',
            'date_faits'    => 'required|date|before_or_equal:today',
            'province'      => 'required|in:Estuaire,Haut-Ogooué,Moyen-Ogooué,Ngounié,Nyanga,Ogooué-Ivindo,Ogooué-Lolo,Ogooué-Maritime,Woleu-Ntem',
            'ville'         => 'required|string|max:100',
            'email_contact' => 'sometimes|nullable|email|max:255',
            // Preuves optionnelles soumises en même temps (upload atomique)
            'fichiers'      => 'sometimes|array|max:5',
            'fichiers.*'    => 'file|max:10240|mimes:jpg,jpeg,png,pdf,mp3,mp4,wav',
        ]);

        $fileData = $request->file('fichiers', []);

        $signalementData         = collect($validated)->except('fichiers')->toArray();
        $signalementData['code'] = $this->generateCode();
        $signalementData['statut'] = 'recu';

        // Nettoyer toutes les preuves AVANT de créer le signalement : un fichier refusé
        // ne laisse pas de signalement orphelin.
        foreach ($fileData as $file) {
            if (!ImageSanitizer::strip($file->getRealPath())) {
                return response()->json(['message' => 'Fichier de preuve non conforme ou illisible'], 422);
            }
        }

        $s = Signalement::create($signalementData);

        // Stocker via le disk configuré (local ou S3/R2)
        foreach ($fileData as $i => $file) {
            $path = $file->store("preuves/{$s->code}");
            Preuve::create([
                'signalement_id' => $s->id,
                'nom_fichier'    => ImageSanitizer::safeName($file, $i),
                'chemin'         => $path,
                'type_fichier'   => $file->getMimeType(),
                'taille'         => $file->getSize(),
            ]);
        }

        return response()->json([
            'code'    => $s->code,
            'message' => 'Signalement enregistré avec succès',
            'preuves' => count($fileData),
        ], 201);
    }

    /** Journalise un code inconnu (détection d'énumération) avec une empreinte d'IP non réversible. */
    private function logCodeInconnu(Request $request, string $action): void
    {
        Log::warning('Code de suivi inconnu', [
            'action' => $action,
            'ip'     => substr(hash_hmac('sha256', (string) $request->ip(), (string) config('app.key')), 0, 16),
        ]);
    }

    public function statut(Request $request, $code)
    {
        $s = Signalement::where('code', $code)->first();
        if (!$s) {
            $this->logCodeInconnu($request, 'statut');
            abort(404);
        }

        return response()->json([
            'code'          => $s->code,
            'statut'        => $s->statut,
            'categorie'     => $s->categorie,
            'province'      => $s->province,
            'updated_at'    => $s->updated_at,
            'message_agent' => $s->message_agent,
        ]);
    }

    public function storePreuves(Request $request, $code)
    {
        $s = Signalement::where('code', $code)->first();
        if (!$s) {
            $this->logCodeInconnu($request, 'preuves');
            abort(404);
        }

        // Vérifier la limite totale (10 preuves max par signalement)
        $existantes = $s->preuves()->count();

        $request->validate([
            'fichiers'   => 'required|array|max:' . max(1, 10 - $existantes),
            'fichiers.*' => 'file|max:10240|mimes:jpg,jpeg,png,pdf,mp3,mp4,wav',
        ]);

        if ($existantes >= 10) {
            return response()->json(['message' => 'Limite de 10 preuves atteinte pour ce signalement'], 422);
        }

        $fichiers = $request->file('fichiers');
        foreach ($fichiers as $file) {
            if (!ImageSanitizer::strip($file->getRealPath())) {
                return response()->json(['message' => 'Fichier de preuve non conforme ou illisible'], 422);
            }
        }

        $preuves = [];
        foreach ($fichiers as $i => $file) {
            $path      = $file->store("preuves/{$code}");
            $preuves[] = Preuve::create([
                'signalement_id' => $s->id,
                'nom_fichier'    => ImageSanitizer::safeName($file, $existantes + $i),
                'chemin'         => $path,
                'type_fichier'   => $file->getMimeType(),
                'taille'         => $file->getSize(),
            ]);
        }

        return response()->json(['message' => count($preuves) . ' preuve(s) ajoutée(s)'], 201);
    }
}
