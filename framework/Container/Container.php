<?php

declare(strict_types=1);

namespace Trash\Container;

use Closure;
use Psr\Container\ContainerInterface;
use Trash\Container\Exceptions\NotFoundException;

class Container implements ContainerInterface
{
    private array $bindings = [];
    private array $singletonBindings = [];
    private array $instances = [];

    private function resolve(callable|string $concrete, array $parameters = []): mixed
    {
        if ($concrete instanceof Closure) {
            return $concrete($this);
        }
        return new $concrete();
    }

    public function bind(string  $abstract, callable|string|null $concrete = null): void
    {
        $this->bindings[$abstract] = $concrete ?? $abstract;
    }

    public function singleton(string $abstract, callable|string|null $concrete = null): void
    {
        $this->singletonBindings[$abstract] = $concrete ?? $abstract;
    }

    public function instance(string $abstract, mixed $instance): void
    {
        $this->instances[$abstract] = $instance;
    }

    public function has(string $id): bool
    {
        return isset($this->bindings[$id]) || isset($this->singletonBindings[$id]) || isset($this->instances[$id]);
    }

    public function make(string $abstract, array $parameters = []): mixed
    {
        return $this->resolve($abstract, $parameters);
    }

    public function get(string $id): mixed
    {
        if (isset($this->instances[$id])) {
            return $this->instances[$id];
        }

        if (isset($this->singletonBindings[$id])) {
            return $this->instances[$id] = $this->resolve($this->singletonBindings[$id]);
        }

        if (isset($this->bindings[$id])) {
            return $this->resolve($this->bindings[$id]);
        }

        throw new NotFoundException("Target [$id] is not bound in the container.");
    }
}
