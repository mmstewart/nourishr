<?php

/**
 * Here is the serverless function entry
 * for deployment with Vercel.
 */
var_dump(base_path('storage/certs/ca.pem'));
var_dump(file_exists(base_path('storage/certs/ca.pem')));
die();

require __DIR__ . '/../public/index.php';
