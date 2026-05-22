<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

require __DIR__ . '/../vendor/autoload.php';

try {
    $app = require_once __DIR__ . '/../bootstrap/app.php';
    $app->bootstrapWith([
        \Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables::class,
        \Illuminate\Foundation\Bootstrap\LoadConfiguration::class,
        \Illuminate\Foundation\Bootstrap\RegisterProviders::class,
        \Illuminate\Foundation\Bootstrap\BootProviders::class,
    ]);

    echo "App booted OK\n";

    // Check DB config
    echo "DB_CONNECTION: " . config('database.default') . "\n";
    echo "DB_HOST set: " . (!empty(env('DB_HOST')) ? 'yes' : 'NO - MISSING') . "\n";
    echo "DB_DATABASE set: " . (!empty(env('DB_DATABASE')) ? 'yes' : 'NO - MISSING') . "\n";
    echo "DB_USERNAME set: " . (!empty(env('DB_USERNAME')) ? 'yes' : 'NO - MISSING') . "\n";
    echo "DB_PASSWORD set: " . (!empty(env('DB_PASSWORD')) ? 'yes' : 'NO - MISSING') . "\n";

    $tables = \Illuminate\Support\Facades\DB::select('SHOW TABLES');
    echo "DB connected OK\n";
    var_dump($tables);
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "FILE: " . $e->getFile() . "\n";
    echo "LINE: " . $e->getLine() . "\n";
}
