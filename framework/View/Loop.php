<?php

declare(strict_types=1);

namespace Trash\View;

class Loop
{
    public readonly int $iteration, $count, $remaining;
    public readonly bool $first, $last;
    public readonly array $items;

    public function __construct(iterable $items, public readonly int $index)
    {
        $items = is_array($items) ? $items : iterator_to_array($items);
        $this->items = $items;
        $this->iteration = $index + 1;
        $this->count = count($items);
        $this->remaining = $this->count - $index - 1;
        $this->first = $index === 0;
        $this->last = $index === $this->count - 1;
    }
}
