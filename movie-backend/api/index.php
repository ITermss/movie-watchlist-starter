<?php

declare(strict_types=1);

// Ensure writable /tmp storage directories exist on Vercel's read-only serverless filesystem
foreach ([
    '/tmp/storage/framework/views',
    '/tmp/storage/framework/cache/data',
    '/tmp/storage/framework/sessions',
    '/tmp/storage/logs',
] as $directory) {
    if (! is_dir($directory)) {
        mkdir($directory, 0755, true);
    }
}

// Prevent Symfony Request from stripping the '/api' prefix when script is at /api/index.php
$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['SCRIPT_FILENAME'] = __DIR__.'/../public/index.php';
$_SERVER['PHP_SELF'] = '/index.php';

require __DIR__.'/../public/index.php';
