<?php

declare(strict_types=1);

namespace Trash\Console;

abstract class Command
{
    protected string $signature = '', $description = '';

    public function __construct(
        protected Input $input,
        protected Output $output
    ) {}

    abstract public function handle(): int;

    public function getSignature(): string
    {
        return $this->signature;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    protected function argument(int $index, mixed $default = null): mixed
    {
        return $this->input->argument($index, $default);
    }

    protected function option(string $name, mixed $default = null): mixed
    {
        return $this->input->option($name, $default);
    }

    protected function hasOption(string $name): bool
    {
        return $this->input->hasOption($name);
    }

    protected function line(string $text = ''): void
    {
        $this->output->line($text);
    }

    protected function info(string $text): void
    {
        $this->output->info($text);
    }

    protected function success(string $text): void
    {
        $this->output->success($text);
    }

    protected function error(string $text): void
    {
        $this->output->error($text);
    }

    protected function comment(string $text): void
    {
        $this->output->comment($text);
    }

    protected function warning(string $text): void
    {
        $this->output->warning($text);
    }

    protected function newLine(int $count = 1): void
    {
        $this->output->newLine($count);
    }

    protected function table(array $headers, array $rows): void
    {
        $this->output->table($headers, $rows);
    }

    protected function confirm(string $question, bool $default = false): bool
    {
        $suffix = $default ? '[Y/n]' : '[y/N]';
        $this->output->line("{$question} {$suffix} ");
        $answer = strtolower(trim(fgets(STDIN) ?: ''));
        if ($answer === '') {
            return $default;
        }
        return in_array($answer, ['y', 'yes'], true);
    }

    protected function ensureDir(string $dir): void
    {
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
    }
}
