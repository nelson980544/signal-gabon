<?php

namespace App\Http\Controllers;

use App\Models\Preuve;
use App\Models\Signalement;
use App\Services\FileCrypto;
use App\Services\ImageSanitizer;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

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

    /**
     * Retire les EXIF, chiffre le fichier puis le stocke (local ou S3/R2).
     * Le nom sur disque est aléatoire : il ne révèle ni le nom d'origine ni le type.
     */
    private function storePreuve(Signalement $s, UploadedFile $file): Preuve
    {
        $mime = $file->getMimeType();
        $size = $file->getSize();
        $name = $file->getClientOriginalName();

        ImageSanitizer::strip($file->getRealPath());
        $path = "preuves/{$s->code}/" . Str::uuid() . '.enc';
        FileCrypto::encryptToDisk($file->getRealPath(), $path);

        return Preuve::create([
            'signalement_id' => $s->id,
            'nom_fichier'    => $name,
            'chemin'         => $path,
            'type_fichier'   => $mime,
            'taille'         => $size,
            'chiffre'        => true,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'categorie'     => 'required|in:Administration publique,Police et gendarmerie,Santé et hôpitaux,Éducation et universités,Douanes et frontières,Justice et tribunaux,Marchés publics,Autre',
            'description'   => 'required|string|min:50',
            'date_faits'    => 'required|date',
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

        $s = Signalement::create($signalementData);

        foreach ($fileData as $file) {
            $this->storePreuve($s, $file);
        }

        return response()->json([
            'code'    => $s->code,
            'message' => 'Signalement enregistré avec succès',
            'preuves' => count($fileData),
        ], 201);
    }

    public function statut($code)
    {
        $s = Signalement::where('code', $code)->firstOrFail();

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
        $s = Signalement::where('code', $code)->firstOrFail();

        // Vérifier la limite totale (10 preuves max par signalement)
        $existantes = $s->preuves()->count();

        $request->validate([
            'fichiers'   => 'required|array|max:' . max(1, 10 - $existantes),
            'fichiers.*' => 'file|max:10240|mimes:jpg,jpeg,png,pdf,mp3,mp4,wav',
        ]);

        if ($existantes >= 10) {
            return response()->json(['message' => 'Limite de 10 preuves atteinte pour ce signalement'], 422);
        }

        $preuves = [];
        foreach ($request->file('fichiers') as $file) {
            $preuves[] = $this->storePreuve($s, $file);
        }

        return response()->json(['message' => count($preuves) . ' preuve(s) ajoutée(s)'], 201);
    }
}
