<?php

declare(strict_types=1);

namespace Tests\Support;

use SessionHandlerInterface;

final class InMemorySessionHandler implements SessionHandlerInterface
{
    public array $data = [];

    public function open(string $path, string $name): bool
    {
        return true;
    }

    public function close(): bool
    {
        return true;
    }

    public function read(string $id): string
    {
        return $this->data[$id] ?? '';
    }

    public function write(string $id, string $data): bool
    {
        $this->data[$id] = $data;
        return true;
    }

    public function destroy(string $id): bool
    {
        unset($this->data[$id]);
        return true;
    }

    public function gc(int $max_lifetime): int
    {
        return 0;
    }
}