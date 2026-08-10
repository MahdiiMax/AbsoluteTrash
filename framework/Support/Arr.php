<?php

declare(strict_types=1);

namespace Trash\Support;

class Arr
{
    public static function getData(mixed $target, string|array $key): mixed
    {
        $segments = is_array($key) ? $key : explode('.', str_replace('->', '.', $key));
        foreach ($segments as $segment) {
            if (is_array($target) && array_key_exists($segment, $target)) {
                $target = $target[$segment];
            } elseif (is_object($target) && isset($target->{$segment})) {
                $target = $target->{$segment};
            } elseif (is_object($target) && method_exists($target, $segment)) {
                $target = $target->{$segment}();
            } else {
                return null;
            }
        }
        return $target;
    }

    public static function get(array $array, string|int|null $key, mixed $default = null): mixed
    {
        if ($key === null) {
            return $array;
        }
        if (array_key_exists($key, $array)) {
            return $array[$key];
        }
        foreach (explode('.', (string)$key) as $segment) {
            if (!is_array($array) || !array_key_exists($segment, $array)) {
                return $default;
            }
            $array = $array[$segment];
        }
        return $array;
    }

    public static function has(array $array, string|int $key): bool
    {
        if (array_key_exists($key, $array)) {
            return true;
        }
        foreach (explode('.', (string)$key) as $segment) {
            if (!is_array($array) || !array_key_exists($segment, $array)) {
                return false;
            }
            $array = $array[$segment];
        }
        return true;
    }

    public static function set(array &$array, string|int|null $key, mixed $value): array
    {
        if ($key === null) {
            return $array = $value;
        }
        $keys = explode('.', (string)$key);
        $ref = &$array;
        while (count($keys) > 1) {
            $segment = array_shift($keys);
            if (!isset($ref[$segment]) || !is_array($ref[$segment])) {
                $ref[$segment] = [];
            }
            $ref = &$ref[$segment];
        }
        $ref[array_shift($keys)] = $value;
        return $array;
    }

    public static function first(array $array, ?callable $callback = null, mixed $default = null): mixed
    {
        if ($callback === null) {
            return $array === [] ? $default : reset($array);
        }
        foreach ($array as $key => $value) {
            if ($callback($value, $key)) {
                return $value;
            }
        }
        return $default;
    }

    public static function last(array $array, ?callable $callback = null, mixed $default = null): mixed
    {
        if ($callback === null) {
            return $array === [] ? $default : end($array);
        }
        foreach (array_reverse($array, true) as $key => $value) {
            if ($callback($value, $key)) {
                return $value;
            }
        }
        return $default;
    }

    public static function only(array $array, array|string $keys): array
    {
        return array_intersect_key($array, array_flip((array) $keys));
    }

    public static function except(array $array, array|string $keys): array
    {
        return array_diff_key($array, array_flip((array) $keys));
    }

    public static function wrap(mixed $value): array
    {
        if ($value === null) {
            return [];
        }
        return is_array($value) ? $value : [$value];
    }

    public static function collapse(array $array): array
    {
        $results = [];
        foreach ($array as $values) {
            if (is_array($values)) {
                $results[] = $values;
            }
        }
        return array_merge([], ...$results);
    }

    public static function pluck(array $array, string|array $value, string|array|null $key = null): array
    {
        $results = [];
        foreach ($array as $item) {
            $itemValue = self::getData($item, $value);
            if ($key === null) {
                $results[] = $itemValue;
                continue;
            }
            $itemKey = self::getData($item, $key);
            if (is_object($itemKey) && method_exists($itemKey, '__toString')) {
                $itemKey = (string)$itemKey;
            }
            $results[$itemKey] = $itemValue;
        }
        return $results;
    }
}
