<?php

// ── Configuration Vercel ──────────────────────────────────────────────────────
// /tmp est le seul dossier accessible en écriture sur Vercel

$tmpStorage = '/tmp/laravel-storage';

// Créer la structure de dossiers storage dans /tmp
foreach ([
    '/app/preuves',
    '/framework/cache/data',
    '/framework/sessions',
    '/framework/views',
    '/logs',
] as $dir) {
    if (!is_dir($tmpStorage . $dir)) {
        @mkdir($tmpStorage . $dir, 0777, true);
    }
}

// Copier la base SQLite vers /tmp si elle n'y est pas encore
$dbDest = '/tmp/database.sqlite';
if (!file_exists($dbDest)) {
    $dbSource = __DIR__ . '/../database/database.sqlite';
    if (file_exists($dbSource)) {
        copy($dbSource, $dbDest);
    }
}

// Variables d'environnement pour Laravel
$envVars = [
    'VERCEL_STORAGE_PATH'  => $tmpStorage,
    'DB_CONNECTION'        => 'sqlite',
    'DB_DATABASE'          => $dbDest,
    'SESSION_DRIVER'       => 'cookie',
    'CACHE_STORE'          => 'array',
    'LOG_CHANNEL'          => 'stderr',
    'APP_ENV'              => 'production',
    'APP_DEBUG'            => 'false',
    // VIEW_COMPILED_PATH doit être défini AVANT le chargement de la config
    // car realpath() échoue si le dossier n'existe pas encore
    'VIEW_COMPILED_PATH'   => $tmpStorage . '/framework/views',
];

foreach ($envVars as $key => $value) {
    $_ENV[$key] = $value;
    putenv("$key=$value");
}

// ── Bootstrap Laravel ─────────────────────────────────────────────────────────
require_once __DIR__ . '/../public/index.php';
