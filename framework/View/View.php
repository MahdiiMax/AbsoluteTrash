<?php

declare(strict_types=1);

namespace Trash\View;

class View
{
    public function __construct(
        private string $name,
        private string $path,
        private array $data,
        private ViewFactory $factory
    ) {}

    public function name(): string
    {
        return $this->name;
    }

    public function path(): string
    {
        return $this->path;
    }

    public function with(array $data): static
    {
        return new static($this->name, $this->path, array_merge($this->data, $data), $this->factory);
    }

    public function render(): string
    {
        $this->factory->incrementRender();
        try {
            $compiled = $this->factory->compile($this->path);
            $__env = $this->factory;
            extract($this->data, EXTR_SKIP);
            ob_start();
            include $compiled;
            return ob_get_clean();
        } finally {
            if ($this->factory->decrementRender() === 0) {
                $this->factory->flushState();
            }
        }
    }

    public function __toString(): string
    {
        return $this->render();
    }
}
