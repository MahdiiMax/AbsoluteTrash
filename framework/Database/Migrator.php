<?php

declare(strict_types=1);

namespace Trash\Database;

class Migrator
{
    public function __construct(private Connection $connection) {}

    public function migrate(): void
    {
        $this->ensureMigrationsTable();
        $ran = $this->getRan();
        $pending = $this->getPendingMigrations($ran);
        foreach ($pending as $file) {
            $this->runMigration($file);
            $this->logMigration($file);
        }
    }

    public function fresh(): void
    {
        $this->dropAllTables();
        $this->migrate();
    }

    private function ensureMigrationsTable(): void
    {
        $driver = config('database.default');
        $sql = match ($driver) {
            'sqlite' => "CREATE TABLE IF NOT EXISTS migrations (id INTEGER PRIMARY KEY AUTOINCREMENT, migration TEXT NOT NULL, batch INTEGER NOT NULL, ran_at DATETIME DEFAULT CURRENT_TIMESTAMP)",
            default  => "CREATE TABLE IF NOT EXISTS migrations (id INTEGER PRIMARY KEY AUTO_INCREMENT, migration VARCHAR(255) NOT NULL, batch INT NOT NULL, ran_at DATETIME DEFAULT CURRENT_TIMESTAMP)",
        };
        $this->connection->run($sql);
    }

    private function getRan(): array
    {
        $rows = $this->connection->select("SELECT migration FROM migrations ORDER BY id");
        return array_column($rows, 'migration');
    }

    private function getPendingMigrations(array $ran): array
    {
        return array_values(array_diff($this->getMigrationFiles(), $ran));
    }

    private function getMigrationFiles(): array
    {
        $path = base_path('database/migrations');
        if (!is_dir($path)) {
            return [];
        }
        $files = glob($path . '/*.php');
        sort($files);
        return array_map(fn($f) => basename($f), $files);
    }

    private function runMigration(string $file): void
    {
        $path = base_path('database/migrations') . DIRECTORY_SEPARATOR . $file;
        $schema = new Schema($this->connection);
        $migration = require $path;
        $migration($schema);
    }

    private function getNextBatch(): int
    {
        $row = $this->connection->selectOne("SELECT MAX(batch) as max_batch FROM migrations");
        return ($row['max_batch'] ?? 0) + 1;
    }

    private function logMigration(string $file): void
    {
        $this->connection->insert('migrations', [
            'migration' => $file,
            'batch' => $this->getNextBatch(),
        ]);
    }

    private function dropAllTables(): void
    {
        $driver = config('database.default');
        if ($driver === 'sqlite') {
            $tables = $this->connection->select(
                "SELECT name FROM sqlite_master WHERE type = 'table' AND name NOT LIKE 'sqlite_%'"
            );
            foreach ($tables as $table) {
                $this->connection->run("DROP TABLE IF EXISTS \"{$table['name']}\"");
            }
        } else {
            $tables = $this->connection->select(
                "SELECT table_name FROM information_schema.tables WHERE table_schema = DATABASE()"
            );
            foreach ($tables as $table) {
                $this->connection->run("DROP TABLE `{$table['table_name']}`");
            }
        }
    }
}
