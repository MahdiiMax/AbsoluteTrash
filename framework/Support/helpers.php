<?php

declare(strict_types=1);

use Trash\Config\Config;
use Trash\Foundation\Application;
use Trash\View\View;
use Trash\View\ViewFactory;

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

function container(): Application
{
    return Application::getInstance();
}

function app(string $abstract = Application::class): mixed
{
    return container()->make($abstract);
}

function resolve(string $abstract, array $parameters = []): mixed
{
    return app()->make($abstract, $parameters);
}

function base_path(string $path = ''): string
{
    return app()->getBasePath() . ($path !== '' ? DIRECTORY_SEPARATOR . $path : '');
}

function app_path(string $path = ''): string
{
    return base_path('app') . ($path !== '' ? DIRECTORY_SEPARATOR . $path : '');
}

function config_path(string $path = ''): string
{
    return base_path('config') . ($path !== '' ? DIRECTORY_SEPARATOR . $path : '');
}

function storage_path(string $path = ''): string
{
    return base_path('storage') . ($path !== '' ? DIRECTORY_SEPARATOR . $path : '');
}

function public_path(string $path = ''): string
{
    return base_path('public') . ($path !== '' ? DIRECTORY_SEPARATOR . $path : '');
}

function resource_path(string $path = ''): string
{
    return base_path('resources') . ($path !== '' ? DIRECTORY_SEPARATOR . $path : '');
}

function fixPathSeparator(string $path): string
{
    return str_replace('/', DIRECTORY_SEPARATOR, $path);
}

function abort(int $code, string $message = ''): never
{
    throw new RuntimeException($message !== '' ? $message : "HTTP {$code}", $code);
}

function dump(mixed ...$values): void
{
    foreach ($values as $value) {
        var_dump($value);
    }
}

function dd(mixed ...$values): never
{
    dump(...$values);
    exit(1);
}

function e(mixed $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function view(string $view, array $data = []): View
{
    return app(ViewFactory::class)->make($view, $data);
}
