<?php

declare(strict_types=1);

namespace Trash\Validation;

use Trash\Database\Connection;

class Validator
{
    private array $errors = [];
    private array $validated = [];

    public function __construct(
        private array $data,
        private array $rules
    ) {
        $this->validate();
    }

    public function passes(): bool
    {
        return empty($this->errors);
    }

    public function fails(): bool
    {
        return !$this->passes();
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function validated(): array
    {
        return $this->validated;
    }

    public function getData(): array
    {
        return $this->data;
    }

    private function validate(): void
    {
        foreach ($this->rules as $field => $ruleString) {
            $fieldRules = is_array($ruleString) ? $ruleString : explode('|', $ruleString);
            $value = $this->data[$field] ?? null;
            foreach ($fieldRules as $rule) {
                $rule = trim($rule);
                if ($rule === '') {
                    continue;
                }
                $this->applyRule($field, $value, $rule);
            }
        }
        $validKeys = array_keys($this->rules);
        $this->validated = array_intersect_key($this->data, array_flip($validKeys));
    }

    private function applyRule(string $field, mixed $value, string $rule): void
    {
        $parts = explode(':', $rule);
        $name = $parts[0];
        $param = $parts[1] ?? null;
        $passed = match ($name) {
            'required'  => $this->validateRequired($field, $value),
            'string'    => $this->validateString($field, $value),
            'email'     => $this->validateEmail($field, $value),
            'numeric'   => $this->validateNumeric($field, $value),
            'boolean'   => $this->validateBoolean($field, $value),
            'min'       => $this->validateMin($field, $value, (int) $param),
            'max'       => $this->validateMax($field, $value, (int) $param),
            'in'        => $this->validateIn($field, $value, $param),
            'confirmed' => $this->validateConfirmed($field, $value),
            'unique'    => $this->validateUnique($field, $value, $param),
            default     => true,
        };
        if (!$passed) {
            $this->errors[$field][] = $this->errorMessage($field, $name, $param);
        }
    }

    private function validateRequired(string $field, mixed $value): bool
    {
        return $value !== null && $value !== '' && $value !== false;
    }

    private function validateString(string $field, mixed $value): bool
    {
        if ($value === null || $value === '') {
            return true;
        }
        return is_string($value);
    }

    private function validateEmail(string $field, mixed $value): bool
    {
        if ($value === null || $value === '') {
            return true;
        }
        return (bool) filter_var($value, FILTER_VALIDATE_EMAIL);
    }

    private function validateNumeric(string $field, mixed $value): bool
    {
        if ($value === null || $value === '') {
            return true;
        }
        return is_numeric($value);
    }

    private function validateBoolean(string $field, mixed $value): bool
    {
        return in_array($value, [true, false, 0, 1, '0', '1', 'true', 'false'], true);
    }

    private function validateMin(string $field, mixed $value, int $min): bool
    {
        if ($value === null || $value === '') {
            return true;
        }
        if (is_string($value)) {
            return mb_strlen($value) >= $min;
        }
        if (is_numeric($value)) {
            return (float) $value >= $min;
        }
        return true;
    }

    private function validateMax(string $field, mixed $value, int $max): bool
    {
        if ($value === null || $value === '') {
            return true;
        }
        if (is_string($value)) {
            return mb_strlen($value) <= $max;
        }
        if (is_numeric($value)) {
            return (float) $value <= $max;
        }
        return true;
    }

    private function validateIn(string $field, mixed $value, ?string $param): bool
    {
        if ($value === null || $value === '') {
            return true;
        }
        $allowed = array_map('trim', explode(',', $param));
        return in_array((string) $value, $allowed, true);
    }

    private function validateConfirmed(string $field, mixed $value): bool
    {
        $confirmField = $field . '_confirmation';
        return isset($this->data[$confirmField]) && $value === $this->data[$confirmField];
    }

    private function validateUnique(string $field, mixed $value, ?string $param): bool
    {
        if ($value === null || $value === '') {
            return true;
        }
        $parts = explode(',', $param);
        $table = $parts[0];
        $column = $parts[1] ?? $field;
        $conn = app(Connection::class);
        $result = $conn->select("SELECT COUNT(*) as count FROM {$table} WHERE {$column} = ?", [$value]);
        return ($result[0]['count'] ?? 0) === 0;
    }

    private function errorMessage(string $field, string $rule, ?string $param): string
    {
        return match ($rule) {
            'required'  => "The {$field} field is required.",
            'string'    => "The {$field} field must be a string.",
            'email'     => "The {$field} field must be a valid email address.",
            'numeric'   => "The {$field} field must be a number.",
            'boolean'   => "The {$field} field must be true or false.",
            'min'       => "The {$field} field must be at least {$param} characters.",
            'max'       => "The {$field} field must not exceed {$param} characters.",
            'in'        => "The {$field} field must be one of: {$param}.",
            'confirmed' => "The {$field} confirmation does not match.",
            'unique'    => "The {$field} has already been taken.",
            default     => "The {$field} field is invalid.",
        };
    }
}
