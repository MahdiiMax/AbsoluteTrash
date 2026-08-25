<?php

declare(strict_types=1);

namespace Trash\Session;

use SessionHandlerInterface;

class Store
{
    private array $attributes = [], $flashData = ['new' => [], 'old' => []];
    private bool $started = false;

    public function __construct(
        private string $id,
        private SessionHandlerInterface $handler
    ) {}

    public function start(): bool
    {
        if ($this->started) {
            return true;
        }
        $data = $this->handler->read($this->id);
        if ($data !== '' && $data !== false) {
            $unserialized = unserialize($data);
            if (is_array($unserialized)) {
                $this->attributes = $unserialized;
            }
        }
        $flash = $this->attributes['_flash'] ?? ['new' => [], 'old' => []];
        $this->flashData['old'] = $flash['new'];
        $this->flashData['new'] = [];
        $this->started = true;
        return true;
    }

    public function save(): void
    {
        if (!$this->started) {
            return;
        }
        foreach ($this->flashData['old'] as $key) {
            unset($this->attributes[$key]);
        }
        $this->attributes['_flash'] = [
            'new' => $this->flashData['new'],
            'old' => [],
        ];
        $this->handler->write($this->id, serialize($this->attributes));
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function all(): array
    {
        return $this->attributes;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->attributes[$key] ?? $default;
    }

    public function set(string $key, mixed $value): void
    {
        $this->attributes[$key] = $value;
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->attributes);
    }

    public function forget(string $key): void
    {
        unset($this->attributes[$key]);
    }

    public function flush(): void
    {
        $this->attributes = [];
    }

    public function push(string $key, mixed $value): void
    {
        if (!is_array($this->attributes[$key] ?? null)) {
            $this->attributes[$key] = [];
        }
        $this->attributes[$key][] = $value;
    }

    public function pull(string $key, mixed $default = null): mixed
    {
        $value = $this->get($key, $default);
        $this->forget($key);
        return $value;
    }

    public function increment(string $key, int $amount = 1): int
    {
        $this->attributes[$key] = ($this->attributes[$key] ?? 0) + $amount;
        return $this->attributes[$key];
    }

    public function decrement(string $key, int $amount = 1): int
    {
        return $this->increment($key, -$amount);
    }

    public function flash(string $key, mixed $value): void
    {
        $this->set($key, $value);
        $this->flashData['new'][] = $key;
    }

    public function reflash(): void
    {
        $this->flashData['new'] = array_merge(
            $this->flashData['new'],
            $this->flashData['old']
        );
    }
}
