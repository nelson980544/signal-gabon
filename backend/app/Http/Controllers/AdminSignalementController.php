<?php

namespace App\Http\Controllers;

use App\Models\Signalement;
use App\Models\SignalementHistorique;
use App\Notifications\StatutSignalementChange;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

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

        $ancienStatut  = $s->statut;
        $ancienAgentId = $s->agent_id;

        $s->update($data);

        $statutChange = isset($data['statut']) && $data['statut'] !== $ancienStatut;
        $agentChange  = array_key_exists('agent_id', $data) && $data['agent_id'] !== $ancienAgentId;

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

            // Notifier le citoyen par email si un email de contact a été fourni
            if ($statutChange && $s->email_contact) {
                Notification::route('mail', $s->email_contact)
                    ->notify(new StatutSignalementChange($s, $ancienStatut));
            }
        }

        return response()->json($s->load('agent'));
    }
}
