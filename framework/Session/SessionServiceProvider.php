<?php

declare(strict_types=1);

namespace Trash\Session;

use Override;
use Trash\Foundation\ServiceProvider;

class SessionServiceProvider extends ServiceProvider
{
    #[Override]
    public function register(): void
    {
        $this->app->singleton(SessionManager::class, fn() => new SessionManager());
    }
}
