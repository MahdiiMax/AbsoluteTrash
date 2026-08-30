<?php

declare(strict_types=1);

namespace Trash\Console;

use Override;
use Trash\Foundation\ServiceProvider;

class ConsoleServiceProvider extends ServiceProvider
{
    #[Override]
    public function register(): void
    {
        $this->app->singleton(CommandRegistry::class, function () {
            $registry = new CommandRegistry();
            $registry->register(config('console.commands', []));
            return $registry;
        });
    }
}
