<?php

declare(strict_types=1);

return [
    'name'  => env('APP_NAME', 'Absolute Trash'),
    'env'   => env('APP_ENV', 'production'),
    'debug' => env('APP_DEBUG', false),
    'url'   => env('APP_URL', 'http://localhost'),
    'providers' => [
        \App\Providers\AppServiceProvider::class,
    ],
];
