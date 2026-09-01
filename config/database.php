<?php

return [

    // Hosting compartido de Hostinger: sin PostgreSQL/PostGIS ni servidor Redis
    // disponibles (solo la extensión PHP de Redis, sin daemon corriendo). MariaDB
    // es el único motor real acá — ver docs/GEO_MARIADB.md para el trade-off
    // geoespacial frente al plan original con PostGIS.
    'default' => env('DB_CONNECTION', 'mysql'),

    'connections' => [

        'mysql' => [
            'driver' => 'mysql',
            'url' => env('DATABASE_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'ruta360'),
            'username' => env('DB_USERNAME', 'ruta360'),
            'password' => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => env('DB_CHARSET', 'utf8mb4'),
            'collation' => env('DB_COLLATION', 'utf8mb4_unicode_ci'),
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => 'InnoDB',
        ],

    ],

    'migrations' => [
        'table' => 'migrations',
        'update_date_on_publish' => true,
    ],

];
