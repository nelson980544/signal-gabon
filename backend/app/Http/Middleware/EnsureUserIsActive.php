<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Révoque l'accès d'un compte désactivé même si son token est encore valide.
 */
class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if ($user && !$user->is_active) {
            $user->tokens()->delete();
            return response()->json(['message' => 'Compte désactivé'], 403);
        }

        return $next($request);
    }
}
