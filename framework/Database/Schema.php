<?php

declare(strict_types=1);

namespace Trash\Database;

class Schema
{
    public function __construct(private Connection $connection) {}

    public function create(string $table, callable $callback): void
    {
        $blueprint = new Blueprint();
        $callback($blueprint);
        $driver = config('database.default');
        $columns = $blueprint->toSql($driver);
        $sql = "CREATE TABLE {$table} (" . implode(', ', $columns) . ")";
        $this->connection->run($sql);
    }

    public function drop(string $table): void
    {
        $this->connection->run("DROP TABLE {$table}");
    }

    public function dropIfExists(string $table): void
    {
        $this->connection->run("DROP TABLE IF EXISTS {$table}");
    }

    public function hasTable(string $table): bool
    {
        $driver = config('database.default');
        if ($driver === 'sqlite') {
            return $this->connection->selectOne(
                "SELECT name FROM sqlite_master WHERE type = 'table' AND name = ?",
                [$table]
            ) !== null;
        }
        return $this->connection->selectOne(
            "SELECT COUNT(*) as cnt FROM information_schema.tables WHERE table_name = ? AND table_schema = DATABASE()",
            [$table]
        )['cnt'] > 0;
    }
}
