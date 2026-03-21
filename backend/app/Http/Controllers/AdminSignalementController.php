<?php

namespace App\Http\Controllers;

use App\Models\Signalement;
use Illuminate\Http\Request;

class AdminSignalementController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $q = Signalement::with(['agent', 'preuves'])->latest();

        if ($user->role === 'agent') {
            $q->where('agent_id', $user->id);
        }

        if ($request->statut)   $q->where('statut', $request->statut);
        if ($request->categorie) $q->where('categorie', $request->categorie);
        if ($request->province) $q->where('province', $request->province);
        if ($request->agent_id && $user->role === 'superviseur') {
            $q->where('agent_id', $request->agent_id);
        }

        return response()->json($q->paginate(20));
    }

    public function show(Request $request, $id)
    {
        $user = $request->user();
        $s = Signalement::with(['agent', 'preuves'])->findOrFail($id);

        if ($user->role === 'agent' && $s->agent_id !== $user->id) {
            abort(403);
        }

        return response()->json($s);
    }

    public function update(Request $request, $id)
    {
        $user = $request->user();
        $s = Signalement::findOrFail($id);

        if ($user->role === 'agent' && $s->agent_id !== $user->id) {
            abort(403);
        }

        $data = $request->validate([
            'statut'        => 'sometimes|in:recu,en_examen,attribue,en_instruction,traite,classe',
            'agent_id'      => 'sometimes|nullable|exists:users,id',
            'message_agent' => 'sometimes|nullable|string',
        ]);

        if ($user->role === 'agent') {
            unset($data['agent_id']);
        }

        $s->update($data);

        return response()->json($s->load('agent'));
    }
}
