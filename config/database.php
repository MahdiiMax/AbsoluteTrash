<?php

declare(strict_types=1);

return [
    'connection' => env('DB_CONNECTION', 'mysql'),
    'host'       => env('DB_HOST', '127.0.0.1'),
    'port'       => env('DB_PORT', 3306),
    'database'   => env('DB_DATABASE', 'absolute_trash'),
    'username'   => env('DB_USERNAME', 'root'),
    'password'   => env('DB_PASSWORD', ''),
];
