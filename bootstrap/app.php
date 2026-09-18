<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
        ]);

        $middleware->alias([
            'role' => \App\Http\Middleware\EnsureUserHasRole::class,
        ]);

        // Beacon de métricas de perfil (GO_CHILE_MODELO_COMERCIAL.md §21): se
        // dispara con navigator.sendBeacon/fetch keepalive al hacer click en
        // WhatsApp/teléfono/web/Instagram, sin token CSRF disponible en ese
        // contexto. No es una acción sensible (solo incrementa un contador).
        $middleware->validateCsrfTokens(except: [
            'operadores/*/click',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
