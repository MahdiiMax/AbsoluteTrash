<?php

declare(strict_types=1);

return [
    'host'       => env('MAIL_HOST', '127.0.0.1'),
    'port'       => env('MAIL_PORT', 587),
    'username'   => env('MAIL_USERNAME'),
    'password'   => env('MAIL_PASSWORD'),
    'encryption' => env('MAIL_ENCRYPTION', 'tls'),
    'from'       => [
        'address' => env('MAIL_FROM_ADDRESS', 'hello@example.com'),
        'name'    => env('MAIL_FROM_NAME', 'Absolute Trash'),
    ],
];
