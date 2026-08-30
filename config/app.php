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
        \Trash\Session\SessionServiceProvider::class,
        \Trash\Auth\AuthServiceProvider::class,
        \Trash\Mail\MailServiceProvider::class,
        \Trash\Filesystem\FilesystemServiceProvider::class,
        \Trash\Console\ConsoleServiceProvider::class,
    ],
    'middleware' => [
        \App\Http\Middleware\AddHeaderMiddleware::class,
        \Trash\Http\Middleware\StartSession::class,
    ],
    'routes' => [
        base_path(fixPathSeparator('routes/web.php')),
    ],
    'controllers' => fixPathSeparator('Http/Controllers'),
    'views' => resource_path(fixPathSeparator('views')),
];
