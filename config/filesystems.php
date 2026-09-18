<?php

return [

    'default' => env('FILESYSTEM_DISK', 'local'),

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app/private'),
            'serve' => true,
            'throw' => false,
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL').'/storage',
            'visibility' => 'public',
            'throw' => false,
        ],

        // Sprint Bitácora GO — subida de portadas para /admin/bitacora.
        // Apunta directo a public/ (sin symlink) a propósito: en hosting
        // compartido storage:link puede no estar disponible o romperse
        // en un redeploy; escribir directo a public/uploads evita esa
        // dependencia por completo.
        'blog_uploads' => [
            'driver' => 'local',
            'root' => public_path('uploads/blog-covers'),
            'url' => env('APP_URL').'/uploads/blog-covers',
            'visibility' => 'public',
            'throw' => false,
        ],

    ],

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];
