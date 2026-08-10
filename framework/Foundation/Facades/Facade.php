<?php

declare(strict_types=1);

namespace Trash\Foundation\Facades;

use Trash\Foundation\Application;

abstract class Facade
{
    protected static ?Application $app = null;

    public static function setFacadeApplication(Application $app): void
    {
        self::$app = $app;
    }

    abstract protected static function getFacadeAccessor(): string;

    public static function __callStatic(string $method, array $arguments): mixed
    {
        $accessor = static::getFacadeAccessor();
        return self::$app->make($accessor)->{$method}(...$arguments);
    }
}
