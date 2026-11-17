<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Fundmeldung: geschützt über den Code im Link. Kein CSRF-Token, damit eine
        // lange offene Seite in der Infosoft-App nicht mit 419 abgelehnt wird.
        $middleware->validateCsrfTokens(except: ['fundmeldung']);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
