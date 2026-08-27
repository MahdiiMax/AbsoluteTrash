<?php

declare(strict_types=1);

namespace Trash\Filesystem\Facades;

use Override;
use Trash\Filesystem\FilesystemManager;
use Trash\Foundation\Facades\Facade;

class Storage extends Facade
{
    #[Override]
    protected static function getFacadeAccessor(): string
    {
        return FilesystemManager::class;
    }   
}
