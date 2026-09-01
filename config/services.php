<?php

return [

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    // Espacio reservado para credenciales de mapas (MapLibre/MapTiler) y
    // futura integración SERNATUR, cuando se reactive el módulo.
    'maptiler' => [
        'key' => env('MAPTILER_KEY'),
    ],

];
