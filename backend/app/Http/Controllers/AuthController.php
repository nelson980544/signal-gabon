<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate(['email' => 'required|email', 'password' => 'required']);

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
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ]
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
        return response()->json([
            'id' => $u->id,
            'name' => $u->name,
            'email' => $u->email,
            'role' => $u->role,
        ]);
    }

    public function debugLogin(Request $request)
    {
        $all = $request->all();
        $jsonAll = $request->json()->all();
        $isJson = $request->isJson();
        $email = $request->input('email');

        try {
            $request->validate(['email' => 'required|email', 'password' => 'required']);
            $validateResult = 'ok';
        } catch (\Illuminate\Validation\ValidationException $e) {
            $validateResult = $e->errors();
        }

        return response()->json([
            'isJson' => $isJson,
            'email' => $email,
            'all' => $all,
            'json_all' => $jsonAll,
            'validate_result' => $validateResult,
        ]);
    }
}
