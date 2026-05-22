<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');

require __DIR__ . '/../vendor/autoload.php';

try {
    $app = require_once __DIR__ . '/../bootstrap/app.php';
    echo "App booted OK\n";

    $db = $app->make('db');
    $tables = $db->select('SHOW TABLES');
    echo "DB connected OK\n";
    var_dump($tables);
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "FILE: " . $e->getFile() . "\n";
    echo "LINE: " . $e->getLine() . "\n";
    echo "\n" . $e->getTraceAsString();
}
