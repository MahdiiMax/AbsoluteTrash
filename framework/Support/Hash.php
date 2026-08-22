<?php

declare(strict_types=1);

namespace Trash\Support;

class Hash
{
    public static function make(string $value): string
    {
        return password_hash($value, PASSWORD_BCRYPT);
    }

    public static function check(string $value, string $hash): bool
    {
        return password_verify($value, $hash);
    }

    public static function needsRehash(string $value): bool
    {
        return password_needs_rehash($value, PASSWORD_BCRYPT);
    }
}
