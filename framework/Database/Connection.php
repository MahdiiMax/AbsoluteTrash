<?php

declare(strict_types=1);

namespace Trash\Database;

use PDO;

class Connection
{
    public function __construct(private PDO $pdo) {}

    public static function fromConfig(array $config): self
    {
        $driver = $config['default'];
        $connection = $config['connections'][$driver];

        return match ($connection['driver']) {
            'sqlite' => self::createSqlite($connection),
            'mysql'  => self::createMysql($connection),
            default  => throw new \RuntimeException("Unsupported driver [{$connection['driver']}]."),
        };
    }

    private static function createSqlite(array $config): self
    {
        $path = $config['database'];
        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        $pdo = new PDO('sqlite:' . $path);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $pdo->exec('PRAGMA journal_mode=WAL');
        return new self($pdo);
    }

    private static function createMysql(array $config): self
    {
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
            $config['host'],
            $config['port'],
            $config['database']
        );
        $pdo = new PDO($dsn, $config['username'], $config['password']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return new self($pdo);
    }

    public function pdo(): PDO
    {
        return $this->pdo;
    }

    public function run(string $sql, array $bindings = []): bool
    {
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($bindings);
    }

    public function select(string $sql, array $bindings = []): array
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($bindings);
        return $stmt->fetchAll();
    }

    public function selectOne(string $sql, array $bindings = []): ?array
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($bindings);
        $result = $stmt->fetch();
        return $result !== false ? $result : null;
    }

    public function insert(string $table, array $data): string
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $this->run("INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})", array_values($data));
        return $this->pdo->lastInsertId();
    }

    public function update(string $table, array $data, string $where, array $whereBindings = []): int
    {
        $set = implode(', ', array_map(fn($col) => "{$col} = ?", array_keys($data)));
        $stmt = $this->pdo->prepare("UPDATE {$table} SET {$set} WHERE {$where}");
        $stmt->execute(array_merge(array_values($data), $whereBindings));
        return $stmt->rowCount();
    }

    public function delete(string $table, string $where, array $bindings = []): int
    {
        $stmt = $this->pdo->prepare("DELETE FROM {$table} WHERE {$where}");
        $stmt->execute($bindings);
        return $stmt->rowCount();
    }

    public function transaction(callable $callback): mixed
    {
        $this->pdo->beginTransaction();
        try {
            $result = $callback($this);
            $this->pdo->commit();
            return $result;
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function table(string $table): QueryBuilder
    {
        return new QueryBuilder($this, $table);
    }
}
