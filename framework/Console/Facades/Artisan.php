<?php

declare(strict_types=1);

namespace Trash\Console\Facades;

use Override;
use Trash\Console\CommandRegistry;
use Trash\Foundation\Facades\Facade;

class Artisan extends Facade
{
    #[Override]
    protected static function getFacadeAccessor(): string
    {
        return CommandRegistry::class;
    }
}
