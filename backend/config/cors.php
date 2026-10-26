<?php

return [
    'paths'                    => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods'          => ['*'],
    // CORS_ALLOWED_ORIGINS : liste séparée par des virgules, renseignée sur le
    // serveur (voir deploy/.env.production.example). À défaut, les origines de
    // développement et les déploiements Vercel connus.
    'allowed_origins'          => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('CORS_ALLOWED_ORIGINS', implode(',', [
            'http://localhost:5173',
            'http://localhost:5174',
            'https://admin-app-xi-livid.vercel.app',
            'https://citizen-app-rho.vercel.app',
            'https://signal-gabon.vercel.app',
        ])))
    ))),
    'allowed_origins_patterns' => ['#^https://.*\.vercel\.app$#'],
    'allowed_headers'          => ['*'],
    'exposed_headers'          => [],
    'max_age'                  => 86400,
    'supports_credentials'     => false,
];
