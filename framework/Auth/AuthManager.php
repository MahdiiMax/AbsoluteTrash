<?php

declare(strict_types=1);

namespace Trash\Auth;

use RuntimeException;
use Trash\Auth\Guards\SessionGuard;

class AuthManager
{
    public function guard(?string $name = null): SessionGuard
    {
        $name ??= config('auth.guard', 'session');
        return match ($name) {
            'session' => app(SessionGuard::class),
            default => throw new RuntimeException("Auth guard [{$name}] is not supported."),
        };
    }
}
