<?php

declare(strict_types=1);

namespace Trash\Database;

use RuntimeException;
use Trash\Support\Collection;
use Trash\Support\Str;

class Model
{
    protected static string $table = '';
    protected static array $fillable = [];
    protected static array $casts = [];
    protected static bool $timestamps = true;

    public array $attributes = [];
    public bool $exists = false;

    public function __construct(array $attributes = [])
    {
        $this->fill($attributes);
    }

    public function __get(string $key): mixed
    {
        return $this->getAttribute($key);
    }

    public function __set(string $key, mixed $value): void
    {
        $this->attributes[$key] = $value;
    }

    public function __isset(string $key): bool
    {
        return array_key_exists($key, $this->attributes);
    }

    public function getAttribute(string $key): mixed
    {
        return $this->castAttribute($key, $this->attributes[$key] ?? null);
    }

    public function setAttribute(string $key, mixed $value): static
    {
        $this->attributes[$key] = $value;
        return $this;
    }

    protected function castAttribute(string $key, mixed $value): mixed
    {
        if (!isset(static::$casts[$key])) {
            return $value;
        }
        return match (static::$casts[$key]) {
            'int'    => (int) $value,
            'float'  => (float) $value,
            'bool'   => (bool) $value,
            'string' => (string) $value,
            default  => $value,
        };
    }

    public function fill(array $attributes): static
    {
        foreach ($attributes as $key => $value) {
            if (in_array($key, static::$fillable)) {
                $this->attributes[$key] = $value;
            }
        }
        return $this;
    }

    public static function getTable(): string
    {
        return static::$table !== ''
            ? static::$table
            : Str::plural(Str::snake(Str::studly(substr(static::class, strrpos(static::class, '\\') + 1))));
    }

    public static function all(): Collection
    {
        $rows = app(Connection::class)->select('SELECT * FROM ' . static::getTable());
        return Collection::make(array_map(fn($row) => static::fromRow($row), $rows));
    }

    public static function find(int $id): ?static
    {
        $row = app(Connection::class)->selectOne(
            'SELECT * FROM ' . static::getTable() . ' WHERE id = ?',
            [$id]
        );
        return $row ? static::fromRow($row) : null;
    }

    public static function findOrFail(int $id): static
    {
        $model = static::find($id);
        if ($model === null) {
            throw new RuntimeException(static::class . " not found.");
        }
        return $model;
    }

    public static function where(string $column, mixed $value): Collection
    {
        $rows = app(Connection::class)->select(
            'SELECT * FROM ' . static::getTable() . ' WHERE ' . $column . ' = ?',
            [$value]
        );
        return Collection::make(array_map(fn($row) => static::fromRow($row), $rows));
    }

    public static function create(array $attributes): static
    {
        $model = new static();
        $model->fill($attributes);
        $model->save();
        return $model;
    }

    public function save(): bool
    {
        if ($this->exists) {
            return $this->update();
        }
        $this->setTimestampsForCreate();
        $id = app(Connection::class)->insert(static::getTable(), $this->attributes);
        $this->id = (int) $id;
        $this->exists = true;
        return true;
    }

    public function update(array $attributes = []): bool
    {
        if ($attributes !== []) {
            $this->fill($attributes);
        }
        if (!$this->exists || !isset($this->attributes['id'])) {
            return false;
        }
        $this->setTimestampForUpdate();
        $id = $this->attributes['id'];
        $data = $this->attributes;
        unset($data['id']);
        if ($data === []) {
            return true;
        }
        app(Connection::class)->update(static::getTable(), $data, 'id = ?', [$id]);
        return true;
    }

    public function delete(): bool
    {
        if (!$this->exists || !isset($this->attributes['id'])) {
            return false;
        }
        $id = $this->attributes['id'];
        app(Connection::class)->delete(static::getTable(), 'id = ?', [$id]);
        $this->exists = false;
        return true;
    }

    public static function fromRow(array $row): static
    {
        $model = new static();
        $model->attributes = $row;
        $model->exists = true;
        return $model;
    }

    public function toArray(): array
    {
        $result = [];
        foreach ($this->attributes as $key => $value) {
            $result[$key] = $this->castAttribute($key, $value);
        }
        return $result;
    }

    public function toJson(int $options = 0): string
    {
        return json_encode($this->toArray(), $options);
    }

    public static function paginate(int $perPage = 15, ?int $page = null): array
    {
        $page = $page ?? max(1, (int) ($_GET['page'] ?? 1));
        $total = (int) app(Connection::class)->selectOne(
            'SELECT COUNT(*) AS total FROM ' . static::getTable()
        )['total'];
        $pages = max(1, (int) ceil($total / $perPage));
        $offset = ($page - 1) * $perPage;
        $rows = app(Connection::class)->select(
            'SELECT * FROM ' . static::getTable() . ' LIMIT ' . (int)$perPage . ' OFFSET ' . (int)$offset
        );
        return [
            'items' => Collection::make(array_map(fn($r) => static::fromRow($r), $rows)),
            'page'  => $page,
            'pages' => $pages,
            'total' => $total,
        ];
    }

    private function setTimestampsForCreate(): void
    {
        if (!static::$timestamps) {
            return;
        }
        $now = date('Y-m-d H:i:s');
        if (!array_key_exists('created_at', $this->attributes)) {
            $this->attributes['created_at'] = $now;
        }
        if (!array_key_exists('updated_at', $this->attributes)) {
            $this->attributes['updated_at'] = $now;
        }
    }

    private function setTimestampForUpdate(): void
    {
        if (static::$timestamps) {
            $this->attributes['updated_at'] = date('Y-m-d H:i:s');
        }
    }
}
