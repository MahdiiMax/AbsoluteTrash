<?php

declare(strict_types=1);

namespace Tests\Unit\Support;

use Tests\TestCase;
use Trash\Support\Collection;

class CollectionTest extends TestCase
{
    public function test_make_all_and_count(): void
    {
        $collection = Collection::make([1, 2, 3]);
        $this->assertSame([1, 2, 3], $collection->all());
        $this->assertSame(3, $collection->count());
    }

    public function test_is_iterable(): void
    {
        $collection = new Collection(['a', 'b']);
        $this->assertSame(['a', 'b'], iterator_to_array($collection));
    }

    public function test_map_preserves_keys(): void
    {
        $collection = new Collection(['a' => 1, 'b' => 2]);
        $mapped = $collection->map(fn(int $v) => $v * 2);
        $this->assertInstanceOf(Collection::class, $mapped);
        $this->assertSame(['a' => 2, 'b' => 4], $mapped->all());
    }

    public function test_filter(): void
    {
        $collection = new Collection([1, 2, 3, 4]);
        $this->assertSame([1 => 2, 3 => 4], $collection->filter(fn($v) => $v % 2 === 0)->all());
    }

     public function test_each_breaks_on_false(): void
    {
        $iteration = 0;
        $collection = new Collection([1, 2, 3]);
        $collection->each(function ($v) use (&$iteration) {
            $iteration++;
            return $v !== 2;
        });
        $this->assertSame(2, $iteration);
    }

    public function test_reduce(): void
    {
        $collection = new Collection([1, 2, 3]);
        $this->assertSame(6, $collection->reduce(fn($carry, $v) => $carry + $v, 0));
    }

    public function test_first_last_sum_min_max(): void
    {
        $collection = new Collection([3, 1, 2]);
        $this->assertSame(3, $collection->first());
        $this->assertSame(2, $collection->last());
        $this->assertSame(6, $collection->sum());
        $this->assertSame(1, $collection->min());
        $this->assertSame(3, $collection->max());
    }

    public function test_sum_with_callback(): void
    {
        $collection = new Collection([['price' => 10], ['price' => 15]]);
        $this->assertSame(25, $collection->sum('price'));
    }

    public function test_chunk(): void
    {
        $collection = new Collection([1, 2, 3, 4, 5]);
        $chunks = $collection->chunk(2);
        $this->assertSame(3, $chunks->count());
        $this->assertSame([1, 2], $chunks->first()->all());
    }

    public function test_merge_and_collapse(): void
    {
        $collection = new Collection([1, 2]);
        $this->assertSame([1, 2, 3, 4], $collection->merge([3, 4])->all());
        $this->assertSame([1, 2, 3], (new Collection([[1, 2], [3]]))->collapse()->all());
    }

     public function test_sort_by_group_by_key_by_unique(): void
    {
        $collection = new Collection([
            ['name' => 'Bob', 'team' => 'a'],
            ['name' => 'Alice', 'team' => 'b'],
            ['name' => 'Carol', 'team' => 'a'],
        ]);
        $this->assertSame(
            ['Alice', 'Bob', 'Carol'],
            array_values(array_map(fn($item) => $item['name'], $collection->sortBy('name')->all()))
        );
        $grouped = $collection->groupBy('team');
        $this->assertSame(['Bob', 'Carol'], $grouped->all()['a']->pluck('name')->all());
        $keyedBy = $collection->keyBy('name');
        $this->assertSame('a', $keyedBy->all()['Bob']['team']);
        $this->assertSame([1, 2, 3], array_values((new Collection([1, 1, 2, 2, 3]))->unique()->all()));
    }

    public function test_to_json_and_string(): void
    {
        $collection = new Collection([1, 2]);
        $this->assertSame('[1,2]', $collection->toJson());
        $this->assertSame('[1,2]', (string) $collection);
    }

    public function test_is_empty(): void
    {
        $this->assertTrue((new Collection())->isEmpty());
        $this->assertFalse((new Collection([1]))->isEmpty());
    }
}