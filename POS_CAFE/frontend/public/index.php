<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine backend base path
$backendPath = file_exists(__DIR__.'/../vendor/autoload.php')
    ? __DIR__.'/..'
    : (file_exists(__DIR__.'/../../backend/vendor/autoload.php') ? __DIR__.'/../../backend' : __DIR__.'/..');

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = $backendPath.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require $backendPath.'/vendor/autoload.php';

// Bootstrap Laravel and handle the request...
(require_once $backendPath.'/bootstrap/app.php')
    ->handleRequest(Request::capture());
