<?php

declare(strict_types=1);

namespace Trash\Container;

use Closure;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionNamedType;
use Trash\Container\Exceptions\NotFoundException;

class Container implements ContainerInterface
{
    private array $bindings = [];
    private array $singletonBindings = [];
    private array $instances = [];

    private function build(string $class, array $parameters = []): object
    {
        $reflection = new ReflectionClass($class);
        if (!$reflection->isInstantiable()) {
            throw new NotFoundException("Target [$class] is not instantiable.");
        }
        $constructor = $reflection->getConstructor();
        if ($constructor === null) {
            return $reflection->newInstance();
        }
        $arguments = [];
        foreach ($constructor->getParameters() as $parameter) {
            $name = $parameter->getName();
            if (array_key_exists($name, $parameters)) {
                $arguments[] = $parameters[$name];
                continue;
            }
            $type = $parameter->getType();
            if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
                $service = $type->getName();
                if ($this->has($service)) {
                    $arguments[] = $this->get($service);
                    continue;
                }
            }
            if ($parameter->isDefaultValueAvailable()) {
                $arguments[] = $parameter->getDefaultValue();
                continue;
            }
            if ($parameter->isVariadic()) {
                continue;
            }

            throw new NotFoundException("Unable to resolve parameter [\${$name}] for class [{$class}].");
        }
        return $reflection->newInstanceArgs($arguments);
    }

    private function resolve(callable|string $concrete, array $parameters = []): mixed
    {
        if ($concrete instanceof Closure) {
            return $concrete($this);
        }
        return $this->build($concrete, $parameters);
    }

    public function bind(string $abstract, callable|string|null $concrete = null): void
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
        return isset($this->bindings[$id]) || isset($this->singletonBindings[$id]) || isset($this->instances[$id]) || class_exists($id);
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

        if (class_exists($id)) {
            return $this->resolve($id);
        }
        throw new NotFoundException("Target [$id] is not bound in the container.");
    }
}
