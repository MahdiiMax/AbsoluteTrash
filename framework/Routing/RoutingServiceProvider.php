<?php

declare(strict_types=1);

namespace Trash\Routing;

use Override;
use Trash\Foundation\ServiceProvider;

class RoutingServiceProvider extends ServiceProvider
{
    #[Override]
    public function register(): void
    {
        $this->app->singleton(RouteCollection::class, fn() => new RouteCollection());
        $this->app->singleton(Router::class, fn() => new Router($this->app->make(RouteCollection::class)));
        $this->app->singleton(RouteScanner::class, fn() => new RouteScanner($this->app->make(Router::class)));
    }

    #[Override]
    public function boot(): void
    {
        $this->app->make(RouteScanner::class)->scan(app_path(config('app.controllers', 'Http/Controllers')));
        foreach (config('app.routes', []) as $file) {
            if (is_file($file)) {
                require $file;
            }
        }
    }
}
