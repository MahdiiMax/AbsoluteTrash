<?php

declare(strict_types=1);

namespace Trash\Filesystem;

use Override;
use Trash\Foundation\ServiceProvider;

class FilesystemServiceProvider extends ServiceProvider
{
    #[Override]
    public function register(): void
    {
        $this->app->singleton(FilesystemManager::class, fn() => new FilesystemManager());
    }
}
