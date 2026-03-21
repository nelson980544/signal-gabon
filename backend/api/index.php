<?php

// Test de base
header('Content-Type: text/plain');

$tmpStorage = '/tmp/laravel-storage';
$dirs = ['/app/preuves', '/framework/cache/data', '/framework/sessions', '/framework/views', '/logs'];
foreach ($dirs as $dir) {
    if (!is_dir($tmpStorage . $dir)) {
        mkdir($tmpStorage . $dir, 0777, true);
    }
}

echo "DIR CHECK:\n";
foreach ($dirs as $dir) {
    echo $tmpStorage . $dir . ": " . (is_dir($tmpStorage . $dir) ? "OK" : "FAIL") . "\n";
}

$dbDest = '/tmp/database.sqlite';
$dbSource = __DIR__ . '/../database/database.sqlite';
echo "DB source: " . (file_exists($dbSource) ? "EXISTS" : "MISSING") . "\n";
echo "DB dest: " . (file_exists($dbDest) ? "EXISTS" : "MISSING") . "\n";

if (!file_exists($dbDest) && file_exists($dbSource)) {
    copy($dbSource, $dbDest);
    echo "DB copied\n";
}

echo "PHP " . PHP_VERSION . "\n";
echo "Done\n";
