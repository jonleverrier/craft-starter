<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// The Laravel app (Craft) lives in ../craft.
$craft = dirname(__DIR__) . '/craft';

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = $craft . '/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require $craft . '/vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once $craft . '/bootstrap/app.php';

$app->handleRequest(Request::capture());
