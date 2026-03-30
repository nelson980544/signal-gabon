<?php

namespace App\Http\Controllers;

use App\Models\Signalement;
use App\Models\Preuve;
use Illuminate\Http\Request;

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
        $max = strlen($chars) - 1;
        for ($i = 0; $i < $length; $i++) {
            $result .= $chars[random_int(0, $max)];
        }
        return $result;
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'categorie'   => 'required|string',
            'description' => 'required|string|min:50',
            'date_faits'  => 'required|date',
            'province'    => 'required|string',
            'ville'       => 'required|string',
            // Preuves optionnelles soumises en même temps (upload atomique)
            'fichiers'    => 'sometimes|array|max:5',
            'fichiers.*'  => 'file|max:10240|mimes:jpg,jpeg,png,pdf,mp3,mp4,wav',
        ]);

        $fileData = $request->file('fichiers', []);

        $signalementData = collect($validated)->except('fichiers')->toArray();
        $signalementData['code']   = $this->generateCode();
        $signalementData['statut'] = 'recu';

        $s = Signalement::create($signalementData);

        // Attacher les preuves immédiatement si fournies (atomique)
        foreach ($fileData as $file) {
            $path = $file->store("preuves/{$s->code}", 'local');
            Preuve::create([
                'signalement_id' => $s->id,
                'nom_fichier'    => $file->getClientOriginalName(),
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

        $request->validate([
            'fichiers'   => 'required|array|max:5',
            'fichiers.*' => 'file|max:10240|mimes:jpg,jpeg,png,pdf,mp3,mp4,wav',
        ]);

        $preuves = [];
        foreach ($request->file('fichiers') as $file) {
            $path = $file->store("preuves/{$code}", 'local');
            $preuves[] = Preuve::create([
                'signalement_id' => $s->id,
                'nom_fichier'    => $file->getClientOriginalName(),
                'chemin'         => $path,
                'type_fichier'   => $file->getMimeType(),
                'taille'         => $file->getSize(),
            ]);
        }

        return response()->json(['message' => count($preuves) . ' preuve(s) ajoutée(s)'], 201);
    }
}
