<?php

declare(strict_types=1);

namespace Trash\Database;

use InvalidArgumentException;
use Trash\Support\Collection;

class QueryBuilder
{
    private string $columns = '*';
    private array $wheres = [];
    private array $bindings = [];
    private array $orders = [];
    private ?int $limitValue = null;
    private ?int $offsetValue = null;

    public function __construct(
        private Connection $connection,
        private string $table
    ) {}

    public function select(string ...$columns): static
    {
        $this->columns = implode(', ', $columns);
        return $this;
    }

    private function addWhere(string $type, string $column, mixed ...$args): static
    {
        if (count($args) === 1) {
            [$operator, $value] = ['=', $args[0]];
        } elseif (count($args) === 2) {
            [$operator, $value] = $args;
        } else {
            throw new InvalidArgumentException('where() requires 2 or 3 arguments.');
        }
        $this->wheres[] = ['type' => $type, 'column' => $column, 'operator' => $operator, 'value' => $value];
        $this->bindings[] = $value;
        return $this;
    }

    public function where(string $column, mixed ...$args): static
    {
        return $this->addWhere('and', $column, ...$args);
    }

    public function orWhere(string $column, mixed ...$args): static
    {
        return $this->addWhere('or', $column, ...$args);
    }

    public function orderBy(string $column, string $direction = 'asc'): static
    {
        $direction = strtolower($direction) === 'desc' ? 'DESC' : 'ASC';
        $this->orders[] = "{$column} {$direction}";
        return $this;
    }

    public function limit(int $limit): static
    {
        $this->limitValue = $limit;
        return $this;
    }

    public function offset(int $offset): static
    {
        $this->offsetValue = $offset;
        return $this;
    }

    private function buildWhere(): array
    {
        if ($this->wheres === []) {
            return ['', []];
        }
        $parts = [];
        $bindings = [];
        foreach ($this->wheres as $i => $w) {
            $clause = "{$w['column']} {$w['operator']} ?";
            $parts[] = $i === 0 ? $clause : strtoupper($w['type']) . ' ' . $clause;
            $bindings[] = $w['value'];
        }
        return [implode(' ', $parts), $bindings];
    }

    private function getBindings(): array
    {
        return $this->bindings;
    }

    private function buildSql(?string $columns = null): string
    {
        $sql = "SELECT " . ($columns ?? $this->columns) . " FROM {$this->table}";
        [$whereSql, $bindings] = $this->buildWhere();
        if ($whereSql !== '') {
            $sql .= " WHERE {$whereSql}";
        }
        if ($this->orders) {
            $sql .= ' ORDER BY ' . implode(', ', $this->orders);
        }
        if ($this->limitValue !== null) {
            $sql .= " LIMIT {$this->limitValue}";
        }
        if ($this->offsetValue !== null) {
            $sql .= " OFFSET {$this->offsetValue}";
        }
        return $sql;
    }

    public function get(): Collection
    {
        return Collection::make(
            $this->connection->select($this->buildSql(), $this->getBindings())
        );
    }

    public function first(): mixed
    {
        $this->limitValue = 1;
        $rows = $this->connection->select($this->buildSql(), $this->getBindings());
        return $rows[0] ?? null;
    }

    public function value(string $column): mixed
    {
        $row = $this->select($column)->first();
        return $row[$column] ?? null;
    }

    public function count(): int
    {
        return (int) $this->connection->selectOne(
            $this->buildSql('COUNT(*) as aggregate'),
            $this->getBindings()
        )['aggregate'];
    }

    public function pluck(string $value, ?string $key = null): Collection
    {
        return $this->get()->pluck($value, $key);
    }

    public function insert(array $data): string
    {
        return $this->connection->insert($this->table, $data);
    }

    public function update(array $data): int
    {
        $set = implode(', ', array_map(fn($col) => "{$col} = ?", array_keys($data)));
        [$whereSql, $whereBindings] = $this->buildWhere();
        $sql = "UPDATE {$this->table} SET {$set}" . ($whereSql !== '' ? " WHERE {$whereSql}" : '');
        $stmt = $this->connection->pdo()->prepare($sql);
        $stmt->execute(array_merge(array_values($data), $whereBindings));
        return $stmt->rowCount();
    }

    public function delete(): int
    {
        [$whereSql, $whereBindings] = $this->buildWhere();
        $sql = "DELETE FROM {$this->table}" . ($whereSql !== '' ? " WHERE {$whereSql}" : '');
        $stmt = $this->connection->pdo()->prepare($sql);
        $stmt->execute($whereBindings);
        return $stmt->rowCount();
    }

    public function toSql(): array
    {
        return [$this->buildSql(), $this->getBindings()];
    }
}
