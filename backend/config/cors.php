<?php

return [
    'paths'                    => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods'          => ['*'],
    'allowed_origins'          => [
        'http://localhost:5173',
        'http://localhost:5174',
        'https://admin-app-xi-livid.vercel.app',
        'https://citizen-app-rho.vercel.app',
        'https://signal-gabon.vercel.app',
    ],
    'allowed_origins_patterns' => ['#^https://.*\.vercel\.app$#'],
    'allowed_headers'          => ['*'],
    'exposed_headers'          => [],
    'max_age'                  => 86400,
    'supports_credentials'     => false,
];
