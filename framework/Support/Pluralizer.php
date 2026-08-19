<?php

declare(strict_types=1);

namespace Trash\Support;

class Pluralizer
{
    protected static ?self $instance = null;

    protected array $irregulars = [
        'person' => 'people',
        'child'  => 'children',
        'man'    => 'men',
        'woman'  => 'women',
        'tooth'  => 'teeth',
        'mouse'  => 'mice',
        'goose'  => 'geese',
    ];

    protected array $uncountable = [
        'sheep',
        'fish',
        'deer',
        'moose',
        'species',
        'series',
        'data',
        'equipment',
        'news',
    ];

    public static function getInstance(): self
    {
        return static::$instance ??= new self();
    }

    public static function plural(string $value): string
    {
        $instance = static::getInstance();
        $lower = Str::lower($value);
        if (in_array($lower, $instance->uncountable)) {
            return $value;
        }
        if (isset($instance->irregulars[$lower])) {
            return $instance->irregulars[$lower];
        }
        if (preg_match('/(?:s|x|z|ch|sh)$/i', $lower)) {
            return $value . 'es';
        }
        if (preg_match('/[^aeiou]y$/i', $lower)) {
            return substr($value, 0, -1) . 'ies';
        }
        if (preg_match('/(?:f|fe)$/i', $lower)) {
            return preg_replace('/(?:f|fe)$/', 'ves', $value);
        }
        return $value . 's';
    }

    public static function singular(string $value): string
    {
        $instance = static::getInstance();
        $lower = Str::lower($value);
        if (in_array($lower, $instance->uncountable)) {
            return $value;
        }
        $flipped = array_flip($instance->irregulars);
        if (isset($flipped[$lower])) {
            return $flipped[$lower];
        }
        if (preg_match('/ies$/i', $lower)) {
            return substr($value, 0, -3) . 'y';
        }
        if (preg_match('/ves$/i', $lower)) {
            return preg_replace('/ves$/', 'fe', $value);
        }
        if (preg_match('/(?:s|x|z|ch|sh)es$/i', $lower)) {
            return substr($value, 0, -2);
        }
        if (preg_match('/s$/i', $lower) && !preg_match('/ss$/i', $lower)) {
            return substr($value, 0, -1);
        }
        return $value;
    }
}
