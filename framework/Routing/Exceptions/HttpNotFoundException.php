<?php

declare(strict_types=1);

namespace Trash\Routing\Exceptions;

use RuntimeException;

class HttpNotFoundException extends RuntimeException
{
    public function __construct(string $message = 'Not Found')
    {
        parent::__construct($message, 404);
    }
}
