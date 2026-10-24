<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate(['email' => 'required|email', 'password' => 'required']);

        // Verrouillage par compte, indépendant de l'IP (X-Forwarded-For falsifiable)
        $key = 'login:' . strtolower($request->input('email'));
        if (RateLimiter::tooManyAttempts($key, 5)) {
            return response()->json(['message' => 'Trop de tentatives. Réessayez plus tard.'], 429)
                ->header('Retry-After', RateLimiter::availableIn($key));
        }

        if (!Auth::attempt($request->only('email', 'password'))) {
            RateLimiter::hit($key, 900);
            return response()->json(['message' => 'Identifiants incorrects'], 401);
        }
        RateLimiter::clear($key);

        $user = Auth::user();
        if (!$user->is_active) {
            return response()->json(['message' => 'Compte désactivé'], 403);
        }

        $token = $user->createToken('agent-token', ['*'], now()->addHours(6))->plainTextToken;

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

    public function refresh(Request $request)
    {
        $user = $request->user();
        // Révoquer l'ancien token et en émettre un nouveau de 6h
        $request->user()->currentAccessToken()->delete();
        $token = $user->createToken('agent-token', ['*'], now()->addHours(6))->plainTextToken;

        return response()->json([
            'token'      => $token,
            'expires_at' => now()->addHours(6)->toISOString(),
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
}
