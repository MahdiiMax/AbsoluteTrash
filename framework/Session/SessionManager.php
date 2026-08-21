<?php

declare(strict_types=1);

namespace Trash\Session;

use RuntimeException;
use Trash\Database\Connection;
use Trash\Session\Handlers\DatabaseHandler;
use Trash\Session\Handlers\FileHandler;
use Trash\Support\Str;

class SessionManager
{
    public function store(?string $id = null): Store
    {
        $driver = config('session.driver', 'file');
        $lifetime = (int) config('session.lifetime', 120);
        $id ??= Str::random(40);
        $handler = match ($driver) {
            'file' => new FileHandler(
                config('session.file.path'),
                $lifetime
            ),
            'database' => new DatabaseHandler(
                app(Connection::class),
                config('session.database.table', 'sessions'),
                $lifetime
            ),
            default => throw new RuntimeException("Session driver [{$driver}] is not supported.")
        };
        return new Store($id, $handler);
    }
}
