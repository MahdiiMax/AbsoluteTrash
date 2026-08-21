<?php

declare(strict_types=1);

return [
    'driver'   => env('SESSION_DRIVER', 'file'),
    'lifetime' => (int) env('SESSION_LIFETIME', 120),
    'cookie'   => env('SESSION_COOKIE', 'absolute_trash_session'),
    'file'     => [
        'path' => storage_path(fixPathSeparator('framework/sessions')),
    ],
    'database' => [
        'table' => 'sessions',
    ],
];
