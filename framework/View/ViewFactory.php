<?php

declare(strict_types=1);

namespace Trash\View;

use LogicException;

class ViewFactory
{
    private array $shared = [], $sections = [], $sectionStack = [];
    private int $renderCount = 0;

    public function __construct(
        private string $path,
        private Compiler $compiler
    ) {}

    public function share(string $key, mixed $value): void
    {
        $this->shared[$key] = $value;
    }

    public function compile(string $path): string
    {
        return $this->compiler->compile($path);
    }

    public function startSection(string $name, ?string $content = null): void
    {
        if ($content !== null) {
            $this->sections[$name] = $content;
            return;
        }
        $this->sectionStack[] = $name;
        ob_start();
    }

    public function stopSection(): void
    {
        $name = array_pop($this->sectionStack);
        if ($name === null) {
            throw new LogicException('Cannot stop a section without first starting one.');
        }
        $this->sections[$name] = ob_get_clean();
    }

    public function setSection(string $name, string $content): void
    {
        $this->sections[$name] = $content;
    }

    public function yieldContent(string $name, string $default = ''): string
    {
        return $this->sections[$name] ?? $default;
    }

    public function incrementRender(): int
    {
        return ++$this->renderCount;
    }

    public function decrementRender(): int
    {
        $this->renderCount = max(0, $this->renderCount - 1);
        return $this->renderCount;
    }

    public function flushState(): void
    {
        $this->sections = [];
        $this->sectionStack = [];
    }

    private function resolve(string $view): string
    {
        $file = rtrim($this->path, '/\\') . DIRECTORY_SEPARATOR . str_replace('.', '/', $view) . '.blade.php';
        if (is_file($file)) {
            return $file;
        }
        throw new ViewNotFoundException("View [{$view}] not found.");
    }

    public function make(string $view, array $data = []): View
      {
        $path = $this->resolve($view);
        return new View($view, $path, array_merge($this->shared, $data), $this);
    }

    public function exists(string $view): bool
    {
        try {
            $this->resolve($view);
            return true;
        } catch (ViewNotFoundException) {
            return false;
        }
    }
}
