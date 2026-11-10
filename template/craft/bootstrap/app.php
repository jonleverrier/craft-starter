<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

// craft/ is the Laravel app; the web root stays at the project's public/, beside it.
return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->create()
    // Set as soon as the app exists, so config files and service providers get the right public_path().
    ->usePublicPath(dirname(__DIR__, 2) . '/public');
