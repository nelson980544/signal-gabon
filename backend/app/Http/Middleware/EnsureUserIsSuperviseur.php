<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureUserIsSuperviseur
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->user()?->role !== 'superviseur') {
            return response()->json(['message' => 'Accès réservé aux superviseurs'], 403);
        }

        return $next($request);
    }
}
