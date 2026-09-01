<?php

return [

    // Sin servidor Redis disponible: cache vía tabla `cache` (MariaDB).
    'default' => env('CACHE_STORE', 'database'),

    'stores' => [

        'array' => [
            'driver' => 'array',
            'serialize' => false,
        ],

        'database' => [
            'driver' => 'database',
            'connection' => env('DB_CACHE_CONNECTION'),
            'table' => env('DB_CACHE_TABLE', 'cache'),
            'lock_connection' => env('DB_CACHE_LOCK_CONNECTION'),
        ],

    ],

    'prefix' => env('CACHE_PREFIX', 'ruta360_cache_'),

];
