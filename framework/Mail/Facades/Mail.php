<?php

declare(strict_types=1);

namespace Trash\Mail\Facades;

use Override;
use Trash\Foundation\Facades\Facade;
use Trash\Mail\Mailer;

class Mail extends Facade
{
    #[Override]
    protected static function getFacadeAccessor(): string
    {
        return Mailer::class;
    }
}
