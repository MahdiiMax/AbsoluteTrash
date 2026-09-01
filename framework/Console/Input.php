<?php

declare(strict_types=1);

namespace Trash\Console;

class Input
{
    private string $command = '';
    private array $arguments = [];
    private array $options = [];

    public function __construct(array $argv)
    {
        $tokens = array_slice($argv, 1);
        if (isset($tokens[0]) && !str_starts_with($tokens[0], '-') && $tokens[0] !== 'list') {
            $this->command = array_shift($tokens);
        }
        for ($i = 0; $i < count($tokens); $i++) {
            $token = $tokens[$i];
            if (str_starts_with($token, '--')) {
                $name = substr($token, 2);
                if (str_contains($name, '=')) {
                    [$k, $v] = explode('=', $name, 2);
                    $this->options[$k] = $v;
                } elseif (isset($tokens[$i + 1]) && !str_starts_with($tokens[$i + 1], '-')) {
                    $this->options[$name] = $tokens[$i + 1];
                    $i++;
                } else {
                    $this->options[$name] = true;
                }
            } elseif (str_starts_with($token, '-') && strlen($token) > 1) {
                $this->options[ltrim($token, '-')] = true;
            } else {
                $this->arguments[] = $token;
            }
        }
    }

    public function command(): string
    {
        return $this->command;
    }

    public function arguments(): array
    {
        return $this->arguments;
    }

    public function options(): array
    {
        return $this->options;
    }

    public function argument(int $index, mixed $default = null): mixed
    {
        return $this->arguments[$index] ?? $default;
    }

    public function option(string $name, mixed $default = null): mixed
    {
        return $this->options[$name] ?? $default;
    }

    public function hasOption(string $name): bool
    {
        return isset($this->options[$name]);
    }
}
