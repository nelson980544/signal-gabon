<?php

// ── Configuration Vercel ──────────────────────────────────────────────────────
// /tmp est le seul dossier accessible en écriture sur Vercel

$tmpStorage = '/tmp/laravel-storage';

foreach ([
    '/app/preuves',
    '/framework/cache/data',
    '/framework/sessions',
    '/framework/views',
    '/logs',
] as $dir) {
    if (!is_dir($tmpStorage . $dir)) {
        mkdir($tmpStorage . $dir, 0777, true);
    }
}

$dbDest = '/tmp/database.sqlite';
if (!file_exists($dbDest)) {
    $dbSource = __DIR__ . '/../database/database.sqlite';
    if (file_exists($dbSource)) {
        copy($dbSource, $dbDest);
    }
}

// Vercel met PATH_INFO sans le préfixe /api (car le fichier est dans api/)
// On restaure REQUEST_URI complet pour que Laravel route correctement
if (isset($_SERVER['REQUEST_URI'])) {
    $_SERVER['PATH_INFO'] = '/' . ltrim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '', '/');
}

$envVars = [
    'VERCEL_STORAGE_PATH'  => $tmpStorage,
    'DB_CONNECTION'        => 'sqlite',
    'DB_DATABASE'          => $dbDest,
    'SESSION_DRIVER'       => 'cookie',
    'CACHE_STORE'          => 'array',
    'LOG_CHANNEL'          => 'stderr',
    'APP_ENV'              => 'production',
    'APP_DEBUG'            => 'true',
    // Rediriger tous les caches vers /tmp (bootstrap/cache est read-only sur Vercel)
    'VIEW_COMPILED_PATH'   => $tmpStorage . '/framework/views',
    'APP_SERVICES_CACHE'   => '/tmp/services.php',
    'APP_PACKAGES_CACHE'   => '/tmp/packages.php',
    'APP_CONFIG_CACHE'     => '/tmp/config.php',
    'APP_ROUTES_CACHE'     => '/tmp/routes-v7.php',
    'APP_EVENTS_CACHE'     => '/tmp/events.php',
];

foreach ($envVars as $key => $value) {
    $_ENV[$key] = $value;
    $_SERVER[$key] = $value;
    putenv("$key=$value");
}

// ── Bootstrap Laravel ─────────────────────────────────────────────────────────
require_once __DIR__ . '/../public/index.php';
