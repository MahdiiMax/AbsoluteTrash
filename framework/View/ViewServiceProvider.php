<?php

declare(strict_types=1);

namespace Trash\View;

use Override;
use Trash\Foundation\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
    #[Override]
    public function register(): void
    {
        $this->app->singleton(Compiler::class, fn() => new Compiler(storage_path(fixPathSeparator('framework/views'))));
        $this->app->singleton(ViewFactory::class, fn() => new ViewFactory(config('app.views', resource_path('views')), $this->app->make(Compiler::class)));
    }
}
