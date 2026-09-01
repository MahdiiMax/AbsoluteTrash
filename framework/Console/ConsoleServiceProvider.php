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
            $registry->register([
                \Trash\Console\Commands\MigrateCommand::class,
                \Trash\Console\Commands\MigrateFreshCommand::class,
                \Trash\Console\Commands\MakeControllerCommand::class,
                \Trash\Console\Commands\MakeModelCommand::class,
                \Trash\Console\Commands\MakeMigrationCommand::class,
                \Trash\Console\Commands\MakeMiddlewareCommand::class,
                \Trash\Console\Commands\RouteListCommand::class,
                \Trash\Console\Commands\CacheClearCommand::class,
                \Trash\Console\Commands\DbSeedCommand::class,
                \Trash\Console\Commands\ServeCommand::class,
            ]);
            $registry->register(config('console.commands', []));
            return $registry;
        });
    }
}
