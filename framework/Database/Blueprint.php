<?php

declare(strict_types=1);

namespace Trash\Database;

class Blueprint
{
    private array $columns = [];
    private ?string $autoIncrement = null;

    public function increments(string $name): static
    {
        $this->columns[] = ['name' => $name, 'type' => 'INTEGER'];
        $this->autoIncrement = $name;
        return $this;
    }

    public function id(): static
    {
        return $this->increments('id');
    }

    public function integer(string $name): static
    {
        $this->columns[] = ['name' => $name, 'type' => 'INTEGER'];
        return $this;
    }

    public function string(string $name, int $length = 255): static
    {
        $this->columns[] = ['name' => $name, 'type' => "VARCHAR({$length})"];
        return $this;
    }

    public function text(string $name): static
    {
        $this->columns[] = ['name' => $name, 'type' => 'TEXT'];
        return $this;
    }

    public function boolean(string $name): static
    {
        $this->columns[] = ['name' => $name, 'type' => 'BOOLEAN'];
        return $this;
    }

    public function timestamp(string $name): static
    {
        $this->columns[] = ['name' => $name, 'type' => 'DATETIME'];
        return $this;
    }

    public function nullable(): static
    {
        $last = &$this->columns[count($this->columns) - 1];
        $last['nullable'] = true;
        return $this;
    }

    public function timestamps(): static
    {
        return $this->timestamp('created_at')->nullable()->timestamp('updated_at')->nullable();
    }

    public function default(mixed $value): static
    {
        $last = &$this->columns[count($this->columns) - 1];
        $last['default'] = $value;
        return $this;
    }

    public function unique(): static
    {
        $last = &$this->columns[count($this->columns) - 1];
        $last['unique'] = true;
        return $this;
    }

    private function quoteDefault(mixed $value): string
    {
        return match (true) {
            is_bool($value) => $value ? '1' : '0',
            is_null($value) => 'NULL',
            is_int($value), is_float($value) => (string) $value,
            default => "'{$value}'",
        };
    }

    public function toSql(string $driver): array
    {
        $parts = [];
        foreach ($this->columns as $col) {
            $type = $col['type'] ?? 'INTEGER';
            $sql = "{$col['name']} {$type}";
            if ($this->autoIncrement === $col['name']) {
                $auto = $driver === 'mysql' ? ' AUTO_INCREMENT' : ' AUTOINCREMENT';
                $sql .= " PRIMARY KEY{$auto}";
            } else {
                if (!($col['nullable'] ?? false)) {
                    $sql .= ' NOT NULL';
                }
                if (array_key_exists('default', $col)) {
                    $sql .= ' DEFAULT ' . $this->quoteDefault($col['default']);
                }
                if ($col['unique'] ?? false) {
                    $sql .= ' UNIQUE';
                }
            }
            $parts[] = $sql;
        }
        return $parts;
    }
}
