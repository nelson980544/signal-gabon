<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $debugContent = $request->getContent();
        $debugIsJson = $request->isJson();

        // Si JSON et contenu disponible mais pas dans les inputs, forcer la fusion
        if ($debugIsJson && !empty($debugContent)) {
            $data = json_decode($debugContent, true) ?? [];
            if (!empty($data)) {
                $request->merge($data);
            }
        }

        try {
            $request->validate(['email' => 'required|email', 'password' => 'required']);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                '_debug' => ['isJson' => $debugIsJson, 'content_len' => strlen($debugContent), 'input_email' => $request->input('email'), 'all' => $request->all()],
                'errors' => $e->errors(),
            ], 422);
        }

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json(['message' => 'Identifiants incorrects'], 401);
        }

        $user = Auth::user();
        if (!$user->is_active) {
            return response()->json(['message' => 'Compte désactivé'], 403);
        }

        $token = $user->createToken('agent-token', ['*'], now()->addHours(8))->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => ['id' => $user->id, 'name' => $user->name, 'email' => $user->email, 'role' => $user->role],
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Déconnecté']);
    }

    public function me(Request $request)
    {
        $u = $request->user();
        return response()->json(['id' => $u->id, 'name' => $u->name, 'email' => $u->email, 'role' => $u->role]);
    }

    public function debugLogin(Request $request)
    {
        return response()->json([
            'obj_id' => spl_object_id($request),
            'isJson' => $request->isJson(),
            'content_raw' => $request->getContent(),
            'json_all' => $request->json()->all(),
        ]);
    }
}
