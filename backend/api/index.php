<?php

// Sur Vercel, rediriger storage vers /tmp (seul dossier accessible en écriture)
$tmpStorage = '/tmp/laravel-storage';

if (!is_dir($tmpStorage)) {
    $dirs = [
        '/app/preuves',
        '/framework/cache/data',
        '/framework/sessions',
        '/framework/views',
        '/logs',
    ];
    foreach ($dirs as $dir) {
        @mkdir($tmpStorage . $dir, 0777, true);
    }
}

$_ENV['VERCEL_STORAGE_PATH'] = $tmpStorage;
putenv('VERCEL_STORAGE_PATH=' . $tmpStorage);

// Copier la base SQLite vers /tmp si elle n'existe pas encore
$dbSource = __DIR__ . '/../database/database.sqlite';
$dbDest   = '/tmp/database.sqlite';

if (!file_exists($dbDest) && file_exists($dbSource)) {
    copy($dbSource, $dbDest);
}

$_ENV['DB_DATABASE']  = $dbDest;
putenv('DB_DATABASE=' . $dbDest);
$_ENV['DB_CONNECTION'] = 'sqlite';
putenv('DB_CONNECTION=sqlite');

require_once __DIR__ . '/../public/index.php';
