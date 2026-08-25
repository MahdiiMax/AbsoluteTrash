<?php

declare(strict_types=1);

namespace Trash\Database;

use Override;
use Trash\Foundation\ServiceProvider;

class DatabaseServiceProvider extends ServiceProvider
{
    #[Override]
    public function register(): void
    {
        $this->app->singleton(Connection::class, fn() => Connection::fromConfig(config('database')));
    }
}
