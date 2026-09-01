<?php

use Laravel\Sanctum\Sanctum;

return [

    // No usamos el modo "SPA con cookies compartidas" (stateful domains) —
    // toda la autenticación de la API es por token Bearer. Se deja vacío
    // a propósito; si en el futuro el frontend Inertia pasa a autenticarse
    // vía cookies contra /api/v1, hay que completar esto y agregar
    // EnsureFrontendRequestsAreStateful al grupo de middleware 'api'.
    'stateful' => [],

    'guard' => ['web'],

    'expiration' => null,

    'token_prefix' => env('SANCTUM_TOKEN_PREFIX', ''),

    'middleware' => [
        'authenticate_session' => Laravel\Sanctum\Http\Middleware\AuthenticateSession::class,
        'encrypt_cookies' => Illuminate\Cookie\Middleware\EncryptCookies::class,
        'validate_csrf_token' => Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
    ],

];
