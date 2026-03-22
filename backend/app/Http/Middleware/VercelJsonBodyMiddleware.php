<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Middleware Vercel : récupère le JSON body capturé tôt dans api/index.php
 * car sur Vercel, php://input n'est lisible qu'une seule fois (cold start).
 */
class VercelJsonBodyMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->isJson() && empty($request->all())) {
            // Essayer d'abord getContent() (cas standard)
            $content = $request->getContent();

            // Fallback : utiliser le global capturé en amont dans api/index.php
            if (empty($content) && !empty($GLOBALS['_vercel_raw_input'])) {
                $content = $GLOBALS['_vercel_raw_input'];
            }

            if (!empty($content)) {
                $data = json_decode($content, true) ?? [];
                if (!empty($data)) {
                    // Initialiser le JSON ParameterBag de la request
                    $request->json()->replace($data);
                    $request->merge($data);
                }
            }
        }
        return $next($request);
    }
}
