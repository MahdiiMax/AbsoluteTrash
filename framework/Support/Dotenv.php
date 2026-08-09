<?php

declare(strict_types=1);

namespace Trash\Support;

class Dotenv
{
    private function isQuoted(string $value): bool
    {
        $length = strlen($value);
        if ($length < 2) {
            return false;
        }
        $first = $value[0];
        $last = $value[$length - 1];
        return ($first === '"' && $last === '"') || ($first === "'" && $last === "'");
    }

    private function parseLine(string $line): void
    {
        $line = trim($line);
        if ($line === '' || $line[0] === '#') {
            return;
        }
        $position = strpos($line, '=');
        if ($position === false) {
            return;
        }
        $key = trim(substr($line, 0, $position));
        $value = trim(substr($line, $position + 1));
        if ($this->isQuoted($value)) {
            $value = substr($value, 1, -1);
        }
        if (getenv($key) === false && !isset($_ENV[$key])) {
            putenv($key . '=' . $value);
            $_ENV[$key] = $value;
        }
    }

    public function load(string $path): void
    {
        $filePath = $path . DIRECTORY_SEPARATOR . '.env';
        if (!is_file($filePath)) {
            return;
        }
        foreach (file($filePath, FILE_IGNORE_NEW_LINES) as $line) {
            $this->parseLine($line);
        }
    }
}
