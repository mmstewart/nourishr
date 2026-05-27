<?php

/**
 * Here is the serverless function entry
 * for deployment with Vercel.
 */
array_map('unlink', glob('/tmp/*.php'));

require __DIR__ . '/../public/index.php';
