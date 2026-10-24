<?php

// Origines exactes uniquement (pas de motif générique *.vercel.app).
// Surcharge via CORS_ALLOWED_ORIGINS (liste séparée par des virgules).
$origins = env('CORS_ALLOWED_ORIGINS')
    ? array_map('trim', explode(',', env('CORS_ALLOWED_ORIGINS')))
    : [
        'http://localhost:5173',
        'http://localhost:5174',
        'https://admin-app-xi-livid.vercel.app',
        'https://citizen-app-rho.vercel.app',
        'https://signal-gabon.vercel.app',
    ];

return [
    'paths'                    => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods'          => ['GET', 'POST', 'PATCH', 'OPTIONS'],
    'allowed_origins'          => $origins,
    'allowed_origins_patterns' => [],
    'allowed_headers'          => ['Content-Type', 'Authorization', 'Accept', 'X-Requested-With'],
    'exposed_headers'          => [],
    'max_age'                  => 86400,
    'supports_credentials'     => false,
];
