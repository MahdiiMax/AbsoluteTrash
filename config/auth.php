<?php

declare(strict_types=1);

return [
    'guard' => env('AUTH_GUARD', 'session'),
    'provider' => [
        'driver' => 'eloquent',
        'model'  => App\Models\User::class,
    ],
    'session' => [
        'key' => '_auth_user_id',
    ],
];
