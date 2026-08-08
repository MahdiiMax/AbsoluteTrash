<?php

declare(strict_types=1);

namespace Trash\Foundation;

class Application
{
    public function __construct(private string $basePath) {}

    public function getBasePath(): string
    {
        return $this->basePath;
    }

    public function handle(): void
    {
        echo "Absolute Trash is running.";
    }
}
