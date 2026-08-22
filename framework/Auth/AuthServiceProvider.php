<?php

declare(strict_types=1);

namespace Trash\Auth;

use Override;
use Trash\Auth\Guards\SessionGuard;
use Trash\Foundation\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    #[Override]
    public function register(): void
    {
        $this->app->singleton(AuthManager::class, fn() => new AuthManager());
        $this->app->singleton(SessionGuard::class, fn() => new SessionGuard());
    }
}
