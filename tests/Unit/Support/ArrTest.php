<?php

declare(strict_types=1);

namespace Tests\Unit\Support;

use Tests\TestCase;
use Trash\Support\Arr;

class ArrTest extends TestCase
{
    public function test_get_with_dot_notation(): void
    {
        $data = ['user' => ['name' => 'Alice', 'address' => ['city' => 'Tehran']]];
        $this->assertSame('Alice', Arr::get($data, 'user.name'));
        $this->assertSame('Tehran', Arr::get($data, 'user.address.city'));
        $this->assertNull(Arr::get($data, 'user.missing'));
        $this->assertSame('fallback', Arr::get($data, 'nope.deep', 'fallback'));
    }

    public function test_has(): void
    {
        $data = ['user' => ['name' => 'Alice']];
        $this->assertTrue(Arr::has($data, 'user.name'));
        $this->assertFalse(Arr::has($data, 'user.email'));
        $this->assertFalse(Arr::has($data, 'nope'));
    }

    public function test_set(): void
    {
        $data = [];
        Arr::set($data, 'user.name', 'Bob');
        $this->assertSame(['user' => ['name' => 'Bob']], $data);
    }

    public function test_get_data_with_objects_and_methods(): void
    {
        $obj = (object) ['name' => 'Alice', 'meta' => (object) ['role' => 'admin']];
        $this->assertSame('Alice', Arr::getData($obj, 'name'));
        $this->assertSame('admin', Arr::getData($obj, ['meta', 'role']));
        $this->assertNull(Arr::getData($obj, ['meta', 'role', 'length']));
    }

    public function test_first_and_last(): void
    {
        $array = [1, 2, 3];
        $this->assertSame(1, Arr::first($array));
        $this->assertSame(3, Arr::last($array));
        $this->assertSame(2, Arr::first($array, fn($v) => $v > 1));
        $this->assertSame(2, Arr::last($array, fn($v) => $v < 3));
        $this->assertNull(Arr::first([]));
        $this->assertNull(Arr::last([]));
    }

    public function test_only_and_except(): void
    {
        $array = ['id' => 1, 'name' => 'Alice', 'email' => 'a@b.c'];
        $this->assertSame(['name' => 'Alice'], Arr::only($array, 'name'));
        $this->assertSame(['id' => 1, 'name' => 'Alice'], Arr::except($array, ['email']));
    }

    public function test_wrap(): void
    {
        $this->assertSame([], Arr::wrap(null));
        $this->assertSame(['a'], Arr::wrap('a'));
        $this->assertSame([1, 2], Arr::wrap([1, 2]));
    }

    public function test_collapse(): void
    {
        $this->assertSame([1, 2, 3, 4], Arr::collapse([[1, 2], [3, 4], 5]));
    }

    public function test_pluck(): void
    {
        $items = [
            ['id' => 1, 'name' => 'Alice'],
            ['id' => 2, 'name' => 'Bob'],
        ];
        $this->assertSame(['Alice', 'Bob'], Arr::pluck($items, 'name'));
        $this->assertSame([1 => 'Alice', 2 => 'Bob'], Arr::pluck($items, 'name', 'id'));
    }
}