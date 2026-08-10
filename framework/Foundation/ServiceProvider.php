<?php

declare(strict_types=1);

namespace Trash\Foundation;

use Trash\Container\Container;

abstract class ServiceProvider
{
    public function __construct(protected Container $app) {}

    abstract public function register(): void;

    public function boot(): void {}
}
