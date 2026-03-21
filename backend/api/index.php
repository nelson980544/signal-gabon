<?php

// ── Configuration Vercel ──────────────────────────────────────────────────────
$tmpStorage = '/tmp/laravel-storage';

foreach (['/app/preuves', '/framework/cache/data', '/framework/sessions', '/framework/views', '/logs'] as $dir) {
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
];

foreach ($envVars as $key => $value) {
    $_ENV[$key] = $value;
    $_SERVER[$key] = $value;
    putenv("$key=$value");
}

// ── Bootstrap Laravel ─────────────────────────────────────────────────────────
try {
    require_once __DIR__ . '/../public/index.php';
} catch (\Throwable $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'error'   => $e->getMessage(),
        'file'    => $e->getFile(),
        'line'    => $e->getLine(),
        'class'   => get_class($e),
        'trace'   => array_slice(array_map(fn($t) => ($t['file'] ?? '?') . ':' . ($t['line'] ?? '?'), $e->getTrace()), 0, 5),
    ]);
}
