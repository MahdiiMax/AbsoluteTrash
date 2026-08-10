<?php

declare(strict_types=1);

namespace Trash\Config;

class Config
{
    private static ?Config $instance = null;

    private function __construct(private array $items) {}

    private static function load(): array
    {
        $items = [];
        $configPath = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR;
        foreach (glob($configPath . '*.php') as $file) {
            $name = basename($file, '.php');
            $items[$name] = require $file;
        }
        return $items;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $value = $this->items;
        foreach (explode('.', $key) as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }
            $value = $value[$segment];
        }
        return $value;
    }

    public static function getInstance(): Config
    {
        if (self::$instance === null) {
            self::$instance = new self(self::load());
        }
        return self::$instance;
    }
}
