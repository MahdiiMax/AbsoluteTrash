<?php

declare(strict_types=1);

namespace Trash\Container\Exceptions;

use Psr\Container\NotFoundExceptionInterface;

class NotFoundException extends \Exception implements NotFoundExceptionInterface {}
