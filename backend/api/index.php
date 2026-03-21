<?php

// ── Configuration Vercel ──────────────────────────────────────────────────────
$tmpStorage = '/tmp/laravel-storage';

foreach (['/app/preuves', '/framework/cache/data', '/framework/sessions', '/framework/views', '/logs'] as $dir) {
    if (!is_dir($tmpStorage . $dir)) mkdir($tmpStorage . $dir, 0777, true);
}

$dbDest = '/tmp/database.sqlite';
if (!file_exists($dbDest)) {
    $dbSource = __DIR__ . '/../database/database.sqlite';
    if (file_exists($dbSource)) copy($dbSource, $dbDest);
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

// Debug: afficher les infos de la requête
header('Content-Type: application/json');
echo json_encode([
    'REQUEST_URI'  => $_SERVER['REQUEST_URI'] ?? 'NOT SET',
    'PATH_INFO'    => $_SERVER['PATH_INFO'] ?? 'NOT SET',
    'SCRIPT_NAME'  => $_SERVER['SCRIPT_NAME'] ?? 'NOT SET',
    'SCRIPT_FILENAME' => $_SERVER['SCRIPT_FILENAME'] ?? 'NOT SET',
    'PHP_SELF'     => $_SERVER['PHP_SELF'] ?? 'NOT SET',
    'HTTP_HOST'    => $_SERVER['HTTP_HOST'] ?? 'NOT SET',
]);
