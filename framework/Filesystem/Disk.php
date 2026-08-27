<?php

declare(strict_types=1);

namespace Trash\Filesystem;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;

class Disk
{
    public function __construct(
        private string $root
    ) {}

    public function get(string $path): string
    {
        $fullPath = $this->path($path);
        if (!is_file($fullPath)) {
            throw new RuntimeException("File not found: {$path}");
        }
        $contents = file_get_contents($fullPath);
        if ($contents === false) {
            throw new RuntimeException("Could not read file: {$path}");
        }
        return $contents;
    }

    public function put(string $path, string $contents): void
    {
        $fullPath = $this->path($path);
        $this->ensureDirectoryExists(dirname($fullPath));
        file_put_contents($fullPath, $contents, LOCK_EX);
    }

    public function append(string $path, string $contents): void
    {
        $fullPath = $this->path($path);
        $this->ensureDirectoryExists(dirname($fullPath));
        file_put_contents($fullPath, $contents, FILE_APPEND | LOCK_EX);
    }

    public function delete(string|array $paths): bool
    {
        $paths = is_array($paths) ? $paths : [$paths];
        foreach ($paths as $path) {
            $fullPath = $this->path($path);
            if (is_file($fullPath)) {
                unlink($fullPath);
            }
        }
        return true;
    }

    public function exists(string $path): bool
    {
        return is_file($this->path($path));
    }

    public function size(string $path): int
    {
        $fullPath = $this->path($path);
        if (!is_file($fullPath)) {
            throw new RuntimeException("File not found: {$path}");
        }
        return filesize($fullPath);
    }

    public function lastModified(string $path): int
    {
        $fullPath = $this->path($path);
        if (!is_file($fullPath)) {
            throw new RuntimeException("File not found: {$path}");
        }
        return filemtime($fullPath);
    }

    public function makeDirectory(string $path, int $mode = 0777, bool $recursive = true): void
    {
        $fullPath = $this->path($path);
        if (!is_dir($fullPath)) {
            mkdir($fullPath, $mode, $recursive);
        }
    }

    public function deleteDirectory(string $path): void
    {
        $fullPath = $this->path($path);
        if (!is_dir($fullPath)) {
            return;
        }
        $items = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($fullPath, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($items as $item) {
            if ($item->isDir()) {
                rmdir($item->getRealPath());
            } else {
                unlink($item->getRealPath());
            }
        }
        rmdir($fullPath);
    }

    public function files(?string $directory = null): array
    {
        $fullPath = $this->path($directory ?? '');
        if (!is_dir($fullPath)) {
            return [];
        }
        return array_filter(glob($fullPath . '/*'), 'is_file');
    }

    public function directories(?string $directory = null): array
    {
        $fullPath = $this->path($directory ?? '');
        if (!is_dir($fullPath)) {
            return [];
        }
        return array_filter(glob($fullPath . '/*'), 'is_dir');
    }

    public function path(string $path = ''): string
    {
        $fullPath = $this->root . ($path !== '' ? DIRECTORY_SEPARATOR . $path : '');
        return $fullPath;
    }

    private function ensureDirectoryExists(string $directory): void
    {
        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }
    }
}