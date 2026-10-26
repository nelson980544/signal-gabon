<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Le contrôle d'activité du compte ne se fait pas qu'à la connexion : un agent
 * désactivé garde sinon un token valable jusqu'à 6 h.
 */
class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if ($user && ! $user->is_active) {
            $user->tokens()->delete();

            return response()->json(['message' => 'Compte désactivé'], 403);
        }

        return $next($request);
    }
}
