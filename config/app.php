<?php

declare(strict_types=1);

return [
    'name'  => env('APP_NAME', 'Absolute Trash'),
    'env'   => env('APP_ENV', 'production'),
    'debug' => env('APP_DEBUG', false),
    'url'   => env('APP_URL', 'http://localhost'),
    'providers' => [
        \App\Providers\AppServiceProvider::class,
        \Trash\Routing\RoutingServiceProvider::class,
    ],
    'middleware' => [
        \App\Http\Middleware\AddHeaderMiddleware::class,
    ],
    'routes' => [
        dirname(__DIR__) . DIRECTORY_SEPARATOR . 'routes' . DIRECTORY_SEPARATOR . 'web.php',
    ],
    'controllers' => 'Http/Controllers',
];
