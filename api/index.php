<?php

/**
 * Here is the serverless function entry
 * for deployment with Vercel.
 */
@unlink('/tmp/config.php');

require __DIR__ . '/../public/index.php';
