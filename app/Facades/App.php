<?php

declare(strict_types=1);

namespace App\Facades;

use Override;
use Trash\Foundation\Application;
use Trash\Foundation\Facades\Facade;

class App extends Facade
{
    #[Override]
    protected static function getFacadeAccessor(): string
    {
        return Application::class;
    }
}
