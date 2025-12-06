<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// FIX: Normalize REQUEST_URI when accessed through /api rewrite
// When root .htaccess rewrites /api/public/filieres to api/public/index.php/public/filieres,
// we need to extract just /public/filieres for Laravel routing
if (isset($_SERVER['REQUEST_URI'])) {
    // Remove /api/public/index.php/ prefix if present (after rewrite)
    if (preg_match('#^/api/public/index\.php/(.+)$#', $_SERVER['REQUEST_URI'], $matches)) {
        $_SERVER['REQUEST_URI'] = '/' . $matches[1];
    }
    // Or if REQUEST_URI starts with /api/ (before rewrite is processed)
    elseif (strpos($_SERVER['REQUEST_URI'], '/api/') === 0) {
        $_SERVER['REQUEST_URI'] = substr($_SERVER['REQUEST_URI'], 4);
    }
}

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());
