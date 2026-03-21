<?php

namespace App\Http\Controllers;

use App\Models\Signalement;
use App\Models\Preuve;
use Illuminate\Http\Request;

class SignalementPublicController extends Controller
{
    private function generateCode(): string
    {
        do {
            $part1 = strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 4));
            $part2 = strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 4));
            $code = "SG-{$part1}-{$part2}";
        } while (Signalement::where('code', $code)->exists());
        return $code;
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'categorie'   => 'required|string',
            'description' => 'required|string|min:50',
            'date_faits'  => 'required|date',
            'province'    => 'required|string',
            'ville'       => 'required|string',
        ]);

        $validated['code']   = $this->generateCode();
        $validated['statut'] = 'recu';

        $s = Signalement::create($validated);

        return response()->json(['code' => $s->code, 'message' => 'Signalement enregistré avec succès'], 201);
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
