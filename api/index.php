<?php

/**
 * Here is the serverless function entry
 * for deployment with Vercel.
 */
$conn = @fsockopen('db-nourishr.e.aivencloud.com', 28016, $errno, $errstr, 5);
var_dump($conn, $errno, $errstr);
die();

require __DIR__ . '/../public/index.php';
