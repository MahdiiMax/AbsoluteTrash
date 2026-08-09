<?php

declare(strict_types=1);

use Trash\Config\Config;

function numericValue(string $value): int|float|string
{
    return is_numeric($value) ? (str_contains($value, '.') ? (float)$value : (int)$value) : $value;
}

function env(string $key, mixed $default = null): mixed
{
    $value = $_ENV[$key] ?? getenv($key);
    if ($value === false || $value === null) {
        return $default;
    }
    return match (strtolower($value)) {
        'true', '(true)' => true,
        'false', '(false)' => false,
        'null', '(null)' => null,
        default => numericValue($value)
    };
}

function config(string $key, mixed $default = null): mixed
{
    return Config::getInstance()->get($key, $default);
}
