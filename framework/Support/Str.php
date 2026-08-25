<?php

declare(strict_types=1);

namespace Trash\Support;

class Str
{
    public static function upper(string $value): string
    {
        return mb_strtoupper($value);
    }

    public static function lower(string $value): string
    {
        return mb_strtolower($value);
    }

    public static function title(string $value): string
    {
        return mb_convert_case($value, MB_CASE_TITLE);
    }

    public static function studly(string $value): string
    {
        return str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $value)));
    }

    public static function camel(string $value): string
    {
        return lcfirst(self::studly($value));
    }

    public static function snake(string $value, string $delimiter = '_'): string
    {
        $value = preg_replace('/(?<!^)[A-Z]/', $delimiter . '$0', $value);
        return self::lower($value);
    }

    public static function kebab(string $value): string
    {
        return self::snake($value, '-');
    }

    public static function plural(string $value): string
    {
        return Pluralizer::plural($value);
    }

    public static function singular(string $value): string
    {
        return Pluralizer::singular($value);
    }

    public static function limit(string $value, int $limit = 100, string $end = '...'): string
    {
        if (mb_strwidth($value) <= $limit) {
            return $value;
        }
        return mb_rtrim(mb_substr($value, 0, $limit)) . $end;
    }

    public static function contains(string $haystack, string $needle): bool
    {
        return $needle !== '' && mb_strpos($haystack, $needle) !== false;
    }

    public static function startsWith(string $haystack, string $needle): bool
    {
        return $needle !== '' && str_starts_with($haystack, $needle);
    }

    public static function endsWith(string $haystack, string $needle): bool
    {
        return $needle !== '' && str_ends_with($haystack, $needle);
    }

    public static function replace(string $search, string $replace, string $subject): string
    {
        return str_replace($search, $replace, $subject);
    }

    public static function before(string $subject, string $search): string
    {
        return $search === '' ? $subject : explode($search, $subject)[0];
    }

    public static function after(string $subject, string $search): string
    {
        if ($search === '') {
            return $subject;
        }
        $parts = explode($search, $subject, 2);
        return $parts[1] ?? $subject;
    }

    public static function uuid(): string
    {
        $bytes = random_bytes(16);
        $bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x40);
        $bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($bytes), 4));
    }

    public static function random(int $length = 16): string
    {
        return bin2hex(random_bytes((int) ceil($length / 2)));
    }
}
