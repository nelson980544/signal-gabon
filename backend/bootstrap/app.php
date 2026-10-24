<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\TrustProxies;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Restreindre via TRUSTED_PROXIES (IP/CIDR séparés par des virgules) quand
        // l'IP du proxy de la plateforme est connue ; '*' par défaut pour ne pas
        // casser les déploiements existants.
        $trusted = env('TRUSTED_PROXIES', '*');
        $middleware->trustProxies(at: $trusted === '*' ? '*' : array_map('trim', explode(',', $trusted)));

        $middleware->alias([
            'active'      => \App\Http\Middleware\EnsureUserIsActive::class,
            'superviseur' => \App\Http\Middleware\EnsureUserIsSuperviseur::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
