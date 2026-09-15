<?php

declare(strict_types=1);

namespace Trash\Database\Exceptions;

use RuntimeException;

class ModelNotFoundException extends RuntimeException
{
    public function __construct(string $message = 'Model not found')
    {
        parent::__construct($message, 404);
    }
}
