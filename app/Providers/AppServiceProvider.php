<?php

declare(strict_types=1);

namespace App\Providers;

use Trash\Config\Config;
use Trash\Foundation\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind('greeting', fn() => 'Hello from provider');
        $this->app->singleton(Config::class, fn() => Config::getInstance());
    }

    public function boot(): void
    {
        $this->app->instance('booted', $this->app->get('greeting'));
    }
}
