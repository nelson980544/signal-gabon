<?php

namespace App\Http\Controllers;

use App\Models\Signalement;
use App\Models\SignalementHistorique;
use App\Notifications\StatutSignalementChange;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;

class AdminSignalementController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $q    = Signalement::with(['agent', 'preuves'])->latest();

        if ($user->role === 'agent') {
            $q->where('agent_id', $user->id);
        }

        if ($request->statut)    $q->where('statut', $request->statut);
        if ($request->categorie) $q->where('categorie', $request->categorie);
        if ($request->province)  $q->where('province', $request->province);
        if ($request->agent_id && $user->role === 'superviseur') {
            $q->where('agent_id', $request->agent_id);
        }

        return response()->json($q->paginate(20));
    }

    public function show(Request $request, $id)
    {
        $s = Signalement::with(['agent', 'preuves', 'historiques.agent'])->findOrFail($id);

        $this->authorize('view', $s);

        return response()->json($s);
    }

    public function update(Request $request, $id)
    {
        $user = $request->user();
        $s    = Signalement::findOrFail($id);

        $this->authorize('update', $s);

        $data = $request->validate([
            'statut'        => 'sometimes|in:recu,en_examen,attribue,en_instruction,traite,classe',
            'agent_id'      => 'sometimes|nullable|exists:users,id',
            'message_agent' => 'sometimes|nullable|string',
        ]);

        if ($user->role === 'agent') {
            unset($data['agent_id']);
        }

        if (isset($data['statut']) && ! $s->peutPasserA($data['statut'], $user)) {
            throw ValidationException::withMessages([
                'statut' => "Le passage de « {$s->statut} » à « {$data['statut']} » n'est pas autorisé.",
            ]);
        }

        $ancienStatut  = $s->statut;
        $ancienAgentId = $s->agent_id;

        // Un formulaire envoie l'identifiant en texte, la base le stocke en
        // entier : sans conversion, ré-envoyer le même agent créait une ligne
        // d'historique à chaque sauvegarde.
        $nouvelAgentId = array_key_exists('agent_id', $data) && $data['agent_id'] !== null
            ? (int) $data['agent_id']
            : null;
        if ($nouvelAgentId !== null) {
            $data['agent_id'] = $nouvelAgentId;
        }

        $statutChange = isset($data['statut']) && $data['statut'] !== $ancienStatut;
        $agentChange  = array_key_exists('agent_id', $data) && $nouvelAgentId !== $ancienAgentId;

        // Le dossier et sa trace changent ensemble ou pas du tout.
        DB::transaction(function () use ($s, $data, $user, $ancienStatut, $ancienAgentId, $statutChange, $agentChange) {
            $s->update($data);

            if ($statutChange || $agentChange) {
                SignalementHistorique::create([
                    'signalement_id'   => $s->id,
                    'agent_id'         => $user->id,
                    'ancien_statut'    => $statutChange ? $ancienStatut : null,
                    'nouveau_statut'   => $statutChange ? $s->statut : null,
                    'ancien_agent_id'  => $agentChange ? $ancienAgentId : null,
                    'nouveau_agent_id' => $agentChange ? $s->agent_id : null,
                    'commentaire'      => $data['message_agent'] ?? null,
                ]);
            }
        });

        // Notifier le citoyen par email si un email de contact a été fourni.
        if ($statutChange && $s->email_contact) {
            try {
                Notification::route('mail', $s->email_contact)
                    ->notify(new StatutSignalementChange($s, $ancienStatut));
            } catch (\Throwable $e) {
                // Le statut est déjà enregistré : une panne SMTP ne doit pas
                // faire croire à l'agent que rien n'a été fait. Ni le message
                // de l'exception (il cite l'adresse du citoyen) ni l'adresse
                // elle-même ne vont au journal.
                Log::warning('Notification de changement de statut non envoyée', [
                    'signalement_id' => $s->id,
                    'erreur'         => $e::class,
                ]);
            }
        }

        return response()->json($s->load('agent'));
    }
}
