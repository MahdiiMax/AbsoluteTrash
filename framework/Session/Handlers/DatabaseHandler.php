<?php

declare(strict_types=1);

namespace Trash\Session\Handlers;

use Override;
use SessionHandlerInterface;
use Trash\Database\Connection;

class DatabaseHandler implements SessionHandlerInterface
{
    public function __construct(
        private Connection $connection,
        private string $table = 'sessions',
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
        return true;
    }

    #[Override]
    public function read(string $id): string|false
    {
        $row = $this->connection->selectOne(
            "SELECT payload FROM {$this->table} WHERE id = ? AND lifetime < ?",
            [$id, time()]
        );
        return $row !== null ? $row['payload'] : false;
    }

    #[Override]
    public function write(string $id, string $data): bool
    {
        $lifetime = time() + ($this->lifetime * 60);
        $lastActivity = time();
        $existing = $this->connection->selectOne(
            "SELECT id FROM {$this->table} WHERE id = ?",
            [$id]
        );
        if ($existing !== null) {
            $this->connection->update(
                $this->table,
                ['payload' => $data, 'last_activity' => $lastActivity, 'lifetime' => $lifetime],
                'id = ?',
                [$id]
            );
        } else {
            $this->connection->insert($this->table, [
                'id' => $id,
                'payload' => $data,
                'last_activity' => $lastActivity,
                'lifetime' => $lifetime
            ]);
        }
        return true;
    }

    #[Override]
    public function destroy(string $id): bool
    {
        $this->connection->delete($this->table, 'id = ?', [$id]);
        return true;
    }

    #[Override]
    public function gc(int $max_lifetime): int|false
    {
        $deleted = $this->connection->delete($this->table, 'last_activity < ?', [$max_lifetime]);
        return $deleted;    
    }
}
