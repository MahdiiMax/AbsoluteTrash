<?php

declare(strict_types=1);

namespace Tests\Unit\Database;

use Tests\TestCase;
use Trash\Support\Collection;

class QueryBuilderTest extends TestCase
{
    public function test_insert_returns_last_insert_id(): void
    {
        $id = $this->connection->table('users')->insert([
            'name' => 'Bob',
            'email' => 'bob@example.com',
            'password' => 'secret',
            'active' => 1,
        ]);
        $this->assertSame('2', $id);
    }

    public function test_count(): void
    {
        $this->assertSame(1, $this->connection->table('users')->count());
    }

    public function test_where_value(): void
    {
        $build = $this->connection->table('users')
            ->where('email', 'alice@example.com')
            ->value('name');
        $this->assertSame('Alice', $build);
    }

    public function test_or_where(): void
    {
        $this->connection->table('users')->insert([
            'name' => 'Bob',
            'email' => 'bob@example.com',
            'password' => 'x',
            'active' => 1,
        ]);
        $names = $this->connection->table('users')
            ->where('name', 'Alice')
            ->orWhere('name', 'Bob')
            ->pluck('name')
            ->sortBy(fn(string $name) => $name);
        $this->assertSame(['Alice', 'Bob'], $names->all());
    }

    public function test_order_by_asc_and_desc(): void
    {
        $this->connection->table('users')->insert([
            'name' => 'Bob',
            'email' => 'bob@example.com',
            'password' => 'x',
            'active' => 1,
        ]);
        $asc = $this->connection->table('users')->orderBy('name', 'asc')->first()['name'];
        $desc = $this->connection->table('users')->orderBy('name', 'desc')->first()['name'];
        $this->assertSame('Alice', $asc);
        $this->assertSame('Bob', $desc);
    }

    public function test_limit_and_offset(): void
    {
        $this->connection->table('users')->insert([
            'name' => 'Bob',
            'email' => 'bob@example.com',
            'password' => 'x',
            'active' => 1,
        ]);
        $row = $this->connection->table('users')
            ->orderBy('id', 'asc')->limit(1)->offset(1)->first();
        $this->assertSame('Bob', $row['name']);
    }

    public function test_select_limits_columns(): void
    {
        $row = $this->connection->table('users')->select('id', 'email')->first();
        $this->assertSame(['id' => 1, 'email' => 'alice@example.com'], $row);
    }

    public function test_pluck(): void
    {
        $emails = $this->connection->table('users')->pluck('email');
        $this->assertInstanceOf(Collection::class, $emails);
        $this->assertSame(['alice@example.com'], $emails->toArray());
    }

    public function test_get_returns_collection(): void
    {
        $rows = $this->connection->table('users')->get();
        $this->assertInstanceOf(Collection::class, $rows);
        $this->assertCount(1, $rows);
    }

    public function test_first_returns_null_when_empty(): void
    {
        $row = $this->connection->table('users')->where('email', 'nobody@example.com')->first();
        $this->assertNull($row);
    }

    public function test_insert_update_delete(): void
    {
        $this->connection->table('users')->insert([
            'name' => 'Bob',
            'email' => 'bob@example.com',
            'password' => 'x',
            'active' => 1,
        ]);
        $updated = $this->connection->table('users')
            ->where('email', 'bob@example.com')
            ->update(['name' => 'Bobby']);
        $this->assertSame(1, $updated);
        $this->assertSame('Bobby', $this->connection->table('users')->where('email', 'bob@example.com')->value('name'));
        $deleted = $this->connection->table('users')
            ->where('email', 'bob@example.com')
            ->delete();
        $this->assertSame(1, $deleted);
        $this->assertSame(1, $this->connection->table('users')->count());
    }
}
