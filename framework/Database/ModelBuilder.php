<?php

declare(strict_types=1);

namespace Trash\Database;

use Trash\Support\Collection;

class ModelBuilder extends QueryBuilder
{
    public function __construct(Connection $connection, string $table, private string $modelClass)
    {
        parent::__construct($connection, $table);
    }

    public function first(): mixed
    {
        $row = parent::first();
        return $row ? $this->modelClass::fromRow($row) : null;
    }

    public function get(): Collection
    {
        return parent::get()->map(fn($row) => $this->modelClass::fromRow($row));
    }

    public function paginate(int $perPage = 15, ?int $page = null): array
    {
        $page = $page ?? max(1, (int) ($_GET['page'] ?? 1));
        $total = parent::count();
        $pages = max(1, (int) ceil($total / $perPage));
        $page  = min($page, $pages);
        parent::limit($perPage)->offset(($page - 1) * $perPage);
        return ['items' => $this->get(), 'page' => $page, 'pages' => $pages, 'total' => $total];
    }
}
