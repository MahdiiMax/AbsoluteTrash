<?php

declare(strict_types=1);

namespace Tests\Unit\View;

use Tests\TestCase;
use Trash\View\Loop;

class LoopTest extends TestCase
{
    public function test_loop_metadata_for_first_and_last(): void
    {
        $loop = new Loop(['a', 'b', 'c'], 0);
        $this->assertSame(1, $loop->iteration);
        $this->assertSame(3, $loop->count);
        $this->assertSame(2, $loop->remaining);
        $this->assertTrue($loop->first);
        $this->assertFalse($loop->last);
        $this->assertSame(['a', 'b', 'c'], $loop->items);
    }

    public function test_loop_metadata_for_middle_and_last(): void
    {
        $middle = new Loop([1, 2, 3], 1);
        $this->assertSame(2, $middle->iteration);
        $this->assertSame(1, $middle->remaining);
        $this->assertFalse($middle->first);
        $this->assertFalse($middle->last);
        $last = new Loop([1, 2, 3], 2);
        $this->assertSame(3, $last->iteration);
        $this->assertSame(0, $last->remaining);
        $this->assertTrue($last->last);
    }

    public function test_loop_accepts_generator(): void
    {
        $generator = (function (): \Generator {
            yield 10;
            yield 20;
        })();
        $loop = new Loop($generator, 1);
        $this->assertSame(2, $loop->count);
        $this->assertSame([10, 20], $loop->items);
    }
}