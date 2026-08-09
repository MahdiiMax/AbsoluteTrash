<?php

declare(strict_types=1);

namespace Trash\Config;

class Config
{
    private static ?array $items = null;

    private static function loadIfNeeded(): void
    {
        if (self::$items !== null) {
            return;
        }
        self::$items = [];
        $configPath = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR;
        foreach (glob($configPath . '*.php') as $file) {
            $name = basename($file, '.php');
            self::$items[$name] = require $file;
        }
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        self::loadIfNeeded();
        $value = self::$items;
        foreach (explode('.', $key) as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }
            $value = $value[$segment];
        }
        return $value;
    }
}
