<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

$autoloadPath = __DIR__.'/../vendor/autoload.php';
$appPath = __DIR__.'/../bootstrap/app.php';
$maintenancePath = __DIR__.'/../storage/framework/maintenance.php';

if (! file_exists($autoloadPath) || ! file_exists($appPath)) {
    $autoloadPath = __DIR__.'/../laravel/vendor/autoload.php';
    $appPath = __DIR__.'/../laravel/bootstrap/app.php';
    $maintenancePath = __DIR__.'/../laravel/storage/framework/maintenance.php';
}

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = $maintenancePath)) {
    require $maintenance;
}

// Register the Composer autoloader...
require $autoloadPath;

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once $appPath;

$app->handleRequest(Request::capture());
