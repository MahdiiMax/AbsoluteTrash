<?php

declare(strict_types=1);

namespace Trash\Auth\Facades;

use Override;
use Trash\Auth\Guards\SessionGuard;
use Trash\Foundation\Facades\Facade;

class Auth extends Facade
{
    #[Override]
    protected static function getFacadeAccessor(): string
    {
        return SessionGuard::class;
    }
}
