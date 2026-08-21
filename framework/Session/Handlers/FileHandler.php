<?php

declare(strict_types=1);

namespace Trash\Session\Handlers;

use Override;
use SessionHandlerInterface;

class FileHandler implements SessionHandlerInterface
{
    public function __construct(
        private string $path,
        private int $lifetime = 120
    ) {}

    #[Override]
    public function open(string $path, string $name): bool
    {
        return true;
    }

    #[Override]
    public function close(): bool
    {
        return false;
    }

    #[Override]
    public function read(string $id): string|false
    {
        $file = $this->path . '/sess_' . $id;
        if (!file_exists($file)) {
            return false;
        }
        $data = file_get_contents($file);
        return $data === false ? false : $data;
    }

    #[Override]
    public function write(string $id, string $data): bool
    {
        $file = $this->path . '/sess_' . $id;
        return file_put_contents($file, $data) !== false;
    }

    #[Override]
    public function destroy(string $id): bool
    {
        $file = $this->path . '/sess_' . $id;
        if (file_exists($file)) {
            return unlink($file);
        }
        return true;
    }

    #[Override]
    public function gc(int $max_lifetime): int|false
    {
        $deleted = 0;
        foreach (glob($this->path . '/sess_*') as $file) {
            if (filemtime($file) < $max_lifetime) {
                unlink($file);
                $deleted++;
            }
        }
        return $deleted;
    }
}
