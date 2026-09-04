<?php

declare(strict_types=1);

namespace Trash\Http;

use RuntimeException;

class ValidationException extends RuntimeException
{
    public function __construct(public array $errors)
    {
        parent::__construct('The given data was invalid.');
    }
}
