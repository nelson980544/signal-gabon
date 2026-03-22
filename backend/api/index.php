<?php

// Supprimer les warnings/deprecated de la sortie HTTP (PHP 8.5)
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ERROR | E_PARSE);

// ── Configuration Vercel ──────────────────────────────────────────────────────
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

// Correctif Vercel : Symfony calcule le path relatif à SCRIPT_NAME.
$_SERVER['SCRIPT_NAME']     = '/index.php';
$_SERVER['SCRIPT_FILENAME'] = __DIR__ . '/../public/index.php';
$_SERVER['PHP_SELF']        = '/index.php';

$envVars = [
    'VERCEL_STORAGE_PATH'  => $tmpStorage,
    'DB_CONNECTION'        => 'sqlite',
    'DB_DATABASE'          => $dbDest,
    'SESSION_DRIVER'       => 'cookie',
    'CACHE_STORE'          => 'array',
    'LOG_CHANNEL'          => 'stderr',
    'APP_ENV'              => 'production',
    'APP_DEBUG'            => 'false',
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

// ── Bootstrap Laravel ──────────────────────────────────────────────────────
define('LARAVEL_START', microtime(true));

if (file_exists($maintenance = __DIR__ . '/../storage/framework/maintenance.php')) {
    require $maintenance;
}

require_once __DIR__ . '/../vendor/autoload.php';

(require_once __DIR__ . '/../bootstrap/app.php')
    ->handleRequest(\Illuminate\Http\Request::capture());
