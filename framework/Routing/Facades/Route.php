<?php

declare(strict_types=1);

namespace Trash\Routing\Facades;

use Override;
use Trash\Foundation\Facades\Facade;
use Trash\Routing\Router;

class Route extends Facade
{
    #[Override]
    protected static function getFacadeAccessor(): string
    {
        return Router::class;
    }
}