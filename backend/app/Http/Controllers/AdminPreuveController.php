<?php

namespace App\Http\Controllers;

use App\Models\Preuve;
use App\Models\Signalement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminPreuveController extends Controller
{
    /**
     * Types réellement acceptés à l'upload (SignalementPublicController).
     * Tout ce qui n'est pas dans cette liste est servi en téléchargement
     * brut : un fichier déposé ne doit jamais pouvoir être rendu par le
     * navigateur comme une page.
     */
    private const TYPES_AFFICHABLES = [
        'image/jpeg',
        'image/png',
        'application/pdf',
        'audio/mpeg',
        'audio/wav',
        'audio/x-wav',
        'video/mp4',
    ];

    public function show(Request $request, Signalement $signalement, Preuve $preuve): StreamedResponse
    {
        $this->authorize('view', $signalement);

        if (! Storage::exists($preuve->chemin)) {
            abort(404, 'Le fichier de cette preuve est introuvable sur le stockage.');
        }

        $affichable = in_array($preuve->type_fichier, self::TYPES_AFFICHABLES, true);
        $telecharger = $request->boolean('telecharger') || ! $affichable;

        // Les identifiants suffisent à retrouver qui a consulté quoi ; le nom
        // du fichier, lui, peut décrire les faits dénoncés.
        Log::info('Consultation d\'une preuve', [
            'agent_id' => $request->user()->id,
            'signalement_id' => $signalement->id,
            'preuve_id' => $preuve->id,
            'telechargement' => $telecharger,
        ]);

        $entetes = [
            'Content-Type' => $affichable ? $preuve->type_fichier : 'application/octet-stream',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
        ];

        return $telecharger
            ? Storage::download($preuve->chemin, $preuve->nom_fichier, $entetes)
            : Storage::response($preuve->chemin, $preuve->nom_fichier, $entetes);
    }
}
