<?php

declare(strict_types=1);

namespace Trash\Filesystem;

use RuntimeException;

class FilesystemManager
{
    private array $disks = [];

    public function disk(?string $name = null): Disk
    {
        $name ??= config('filesystem.default', 'local');
        if (isset($this->disks[$name])) {
            return $this->disks[$name];
        }
        $config = config("filesystem.disks.{$name}");
        if ($config === null) {
            throw new RuntimeException("Disk [{$name}] is not configured.");
        }
        $driver = $config['driver'] ?? 'local';
        $root = $config['root'] ?? throw new RuntimeException("Disk [{$name}] has no root path.");
        $this->disks[$name] = match ($driver) {
            'local' => new Disk($root),
            default => throw new RuntimeException("Disk driver [{$driver}] is not supported."),
        };
        return $this->disks[$name];
    }
}
