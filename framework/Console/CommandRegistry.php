<?php

declare(strict_types=1);

namespace Trash\Console;

use RuntimeException;

class CommandRegistry
{
    private array $classes = [];

    public function register(array $commands): void
    {
        foreach ($commands as $class) {
            $this->classes[] = $class;
        }
    }

    public function run(Input $input, Output $output): int
    {
        $signature = $input->command();
        if ($signature === '' || $signature === 'list') {
            $this->printHelp($output);
            return 0;
        }
        $command = $this->find($signature, $input, $output);
        if ($command === null) {
            $output->error("Command [{$signature}] is not defined.");
            return 1;
        }
        try {
            return $command->handle();
        } catch (RuntimeException $e) {
            $output->error($e->getMessage());
            return 1;
        }
    }

    public function getCommands(): array
    {
        return $this->classes;
    }

    private function find(string $signature, Input $input, Output $output): ?Command
    {
        foreach ($this->classes as $class) {
            $probe = new $class($input, $output);
            if ($probe->getSignature() === $signature) {
                return $probe;
            }
        }
        return null;
    }

    private function printHelp(Output $output): void
    {
        $output->line('AbsoluteTrash Console');
        $output->newLine();
        $output->table(['Command', 'Description'], $this->commandTable());
    }

    private function commandTable(): array
    {
        $helper = new Output();
        $rows = [];
        foreach ($this->classes as $class) {
            $probe = new $class(new Input([]), $helper);
            $rows[] = [$probe->getSignature(), $probe->getDescription()];
        }
        return $rows;
    }
}
