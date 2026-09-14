<?php

declare(strict_types=1);

namespace Trash\Support;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Traversable;
use Override;

class Collection implements IteratorAggregate, Countable
{
    public function __construct(protected array $items = []) {}

    #[Override]
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }
    
    private function valueRetriever(callable|string|null $callback): callable
    {
        if ($callback === null) {
            return fn($value) => $value;
        }
        if (is_string($callback)) {
            return fn($value, $key = null) => Arr::getData($value, $callback);
        }
        return $callback;
    }

    public static function make(mixed $items = []): static
    {
        return new static(Arr::wrap($items));
    }

    public function all(): array
    {
        return $this->items;
    }

    public function toArray(): array
    {
        return $this->items;
    }

    public function toJson(int $options = 0): string
    {
        return json_encode($this->toArray(), $options);
    }

    public function __toString(): string
    {
        return $this->toJson();
    }

    public function count(): int
    {
        return count($this->items);
    }

    public function isEmpty(): bool
    {
        return $this->items === [];
    }

    public function isNotEmpty(): bool
    {
        return $this->items !== [];
    }

    public function map(callable $callback): static
    {
        $keys = array_keys($this->items);
        $items = array_map($callback, $this->items, $keys);
        return new static(array_combine($keys, $items));
    }

    public function filter(?callable $callback = null): static
    {
        if ($callback === null) {
            return new static(array_filter($this->items));
        }
        return new static(array_filter($this->items, $callback, ARRAY_FILTER_USE_BOTH));
    }
    public function each(callable $callback): static
    {
        foreach ($this->items as $key => $item) {
            if ($callback($item, $key) === false) {
                break;
            }
        }
        return $this;
    }

    public function reduce(callable $callback, mixed $initial = null): mixed
    {
        return array_reduce($this->items, $callback, $initial);
    }

    public function first(?callable $callback = null, mixed $default = null): mixed
    {
        return Arr::first($this->items, $callback, $default);
    }

    public function last(?callable $callback = null, mixed $default = null): mixed
    {
        return Arr::last($this->items, $callback, $default);
    }

    public function pluck(string|array $value, string|array|null $key = null): static
    {
        return new static(Arr::pluck($this->items, $value, $key));
    }

    public function chunk(int $size, bool $preserveKeys = true): static
    {
        $chunks = array_chunk($this->items, $size, $preserveKeys);
        return new static(array_map(fn($chunk) => new static($chunk), $chunks));
    }

    public function collapse(): static
    {
        return new static(Arr::collapse($this->items));
    }

    public function merge(iterable $items): static
    {
        if ($items instanceof self) {
            $items = $items->all();
        } elseif (!is_array($items)) {
            $items = iterator_to_array($items);
        }
        return new static(array_merge($this->items, $items));
    }

    public function sum(callable|string|null $callback = null): int|float
    {
        if ($callback === null) {
            return array_sum($this->items);
        }
        $callback = $this->valueRetriever($callback);
        return array_sum(array_map($callback, $this->items, array_keys($this->items)));
    }

    public function min(callable|string|null $callback = null): mixed
    {
        if ($callback === null) {
            return $this->items === [] ? null : min($this->items);
        }
        $callback = $this->valueRetriever($callback);
        $min = null;
        foreach ($this->items as $key => $value) {
            $computed = $callback($value, $key);

            if ($min === null || $computed < $min) {
                $min = $computed;
            }
        }
        return $min;
    }

    public function max(callable|string|null $callback = null): mixed
    {
        if ($callback === null) {
            return $this->items === [] ? null : max($this->items);
        }
        $callback = $this->valueRetriever($callback);
        $max = null;
        foreach ($this->items as $key => $value) {
            $computed = $callback($value, $key);

            if ($max === null || $computed > $max) {
                $max = $computed;
            }
        }
        return $max;
    }

    public function sortBy(callable|string $callback, bool $descending = false, int $options = SORT_REGULAR): static
    {
        $callback = $this->valueRetriever($callback);
        $results = [];
        foreach ($this->items as $key => $value) {
            $results[$key] = $callback($value, $key);
        }
        $descending ? arsort($results, $options) : asort($results, $options);
        $ordered = [];
        foreach ($results as $key => $value) {
            $ordered[$key] = $this->items[$key];
        }
        return new static($ordered);
    }

    public function groupBy(callable|string $groupBy): static
    {
        $groupBy = $this->valueRetriever($groupBy);
        $results = [];
        foreach ($this->items as $key => $value) {
            $groupKey = $groupBy($value, $key);
            if (!array_key_exists($groupKey, $results)) {
                $results[$groupKey] = new static();
            }
            $results[$groupKey]->items[] = $value;
        }
        return new static($results);
    }

    public function keyBy(callable|string $keyBy): static
    {
        $keyBy = $this->valueRetriever($keyBy);
        $results = [];
        foreach ($this->items as $item) {
            $resolvedKey = $keyBy($item);
            if (is_object($resolvedKey) && method_exists($resolvedKey, '__toString')) {
                $resolvedKey = (string) $resolvedKey;
            }
            $results[$resolvedKey] = $item;
        }
        return new static($results);
    }

    public function unique(callable|string|null $key = null): static
    {
        $exists = [];
        $results = [];
        foreach ($this->items as $index => $item) {
            $computed = $key === null
                ? $item
                : (is_string($key) ? Arr::getData($item, $key) : $key($item));

            if (!in_array($computed, $exists, true)) {
                $exists[] = $computed;
                $results[$index] = $item;
            }
        }
        return new static($results);
    }
}
