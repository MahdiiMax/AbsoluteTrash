<?php

declare(strict_types=1);

namespace Trash\View\Facades;

use Trash\Foundation\Facades\Facade;
use Trash\View\ViewFactory;

class View extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return ViewFactory::class;
    }
}
