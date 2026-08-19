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
        \Trash\View\ViewServiceProvider::class,
        \Trash\Database\DatabaseServiceProvider::class,
    ],
    'middleware' => [
        \App\Http\Middleware\AddHeaderMiddleware::class,
    ],
    'routes' => [
        base_path(fixPathSeparator('routes/web.php')),
    ],
    'controllers' => fixPathSeparator('Http/Controllers'),
    'views' => resource_path(fixPathSeparator('views')),
];
