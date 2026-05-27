<?php

/**
 * Here is the serverless function entry
 * for deployment with Vercel.
 */
try {
    $pdo = new PDO(
        'mysql:host=db-nourishr.e.aivencloud.com;port=28016;dbname=defaultdb',
        'avnadmin',
        'REDACTED_AIVEN_PASSWORD',
        [
            PDO::MYSQL_ATTR_SSL_CA => __DIR__ . '/../storage/certs/ca.pem',
            PDO::ATTR_TIMEOUT => 5,
        ]
    );

    var_dump('connected');
} catch (Exception $e) {
    var_dump($e->getMessage());
}

die();

require __DIR__ . '/../public/index.php';
