<?php

declare(strict_types=1);

namespace Trash\Console;

class Output
{
    private bool $colorize;

    public function __construct()
    {
        $this->colorize = function_exists('posix_isatty') ? @posix_isatty(STDOUT) : true;
    }

    public function line(string $text = ''): void
    {
        fwrite(STDOUT, $text . PHP_EOL);
    }

    public function info(string $text): void
    {
        $this->line($this->colorize ? "\033[34m{$text}\033[0m" : $text);
    }

    public function success(string $text): void
    {
        $this->line($this->colorize ? "\033[32m{$text}\033[0m" : $text);
    }

    public function error(string $text): void
    {
        $this->line($this->colorize ? "\033[31m{$text}\033[0m" : $text);
    }

    public function comment(string $text): void
    {
        $this->line($this->colorize ? "\033[90m{$text}\033[0m" : $text);
    }

    public function warning(string $text): void
    {
        $this->line($this->colorize ? "\033[33m{$text}\033[0m" : $text);
    }

    public function newLine(int $count = 1): void
    {
        fwrite(STDOUT, str_repeat(PHP_EOL, $count));
    }

    public function table(array $headers, array $rows): void
    {
        $widths = [];
        foreach ($headers as $i => $header) {
            $max = strlen($header);
            foreach ($rows as $row) {
                $len = strlen((string) ($row[$i] ?? ''));
                if ($len > $max) {
                    $max = $len;
                }
            }
            $widths[$i] = $max;
        }

        $line = '+' . implode('+', array_map(fn($w) => str_repeat('-', $w + 2), $widths)) . '+';
        $this->line($line);
        $this->line($this->formatRow($headers, $widths));
        $this->line($line);
        foreach ($rows as $row) {
            $this->line($this->formatRow($row, $widths));
        }
        $this->line($line);
    }

    private function formatRow(array $row, array $widths): string
    {
        $cells = [];
        foreach ($widths as $i => $width) {
            $cells[] = ' ' . str_pad((string) ($row[$i] ?? ''), $width) . ' ';
        }
        return '|' . implode('|', $cells) . '|';
    }
}
