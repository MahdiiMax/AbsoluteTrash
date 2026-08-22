<?php

declare(strict_types=1);

namespace Trash\Session\Facades;

use Override;
use Trash\Foundation\Facades\Facade;
use Trash\Session\SessionManager;

class Session extends Facade
{
    #[Override]
    protected static function getFacadeAccessor(): string
    {
        return SessionManager::class;
    }
}
