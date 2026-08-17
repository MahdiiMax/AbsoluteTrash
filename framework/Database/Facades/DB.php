<?php

declare(strict_types=1);

namespace Trash\Database\Facades;

use Override;
use Trash\Database\Connection;
use Trash\Foundation\Facades\Facade;

class DB extends Facade
{
    #[Override]
    protected static function getFacadeAccessor(): string
    {
        return Connection::class;
    }
}
