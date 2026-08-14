<?php

declare(strict_types=1);

namespace Trash\Container;

use Closure;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use Trash\Container\Exceptions\NotFoundException;

class Container implements ContainerInterface
{
    private array $bindings = [];
    private array $singletonBindings = [];
    private array $instances = [];

    private function coerce(mixed $value, ReflectionParameter $parameter): mixed
    {
        if ($value === null || !is_scalar($value)) {
            return $value;
        }
        $type = $parameter->getType();
        if (!$type instanceof ReflectionNamedType || !$type->isBuiltin()) {
            return $value;
        }
        return match ($type->getName()) {
            'int'    => is_numeric($value) ? (int) $value : $value,
            'float'  => is_numeric($value) ? (float) $value : $value,
            'string' => (string) $value,
            'bool'   => in_array($value, [true, false, 1, 0, '1', '0', 'true', 'false'], true)
                ? filter_var($value, FILTER_VALIDATE_BOOLEAN)
                : $value,
            default  => $value,
        };
    }

    private function resolveArguments(ReflectionFunctionAbstract $reflection, array $parameters): array
    {
        $arguments = [];
        foreach ($reflection->getParameters() as $parameter) {
            $name = $parameter->getName();
            if (array_key_exists($name, $parameters)) {
                $arguments[] = $this->coerce($parameters[$name], $parameter);
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
            throw new NotFoundException("Unable to resolve parameter [\${$name}] for callable.");
        }
        return $arguments;
    }

    private function resolveCallable(callable|array $callable): callable|array
    {
        if (
            is_array($callable)
            && is_string($callable[0])
            && class_exists($callable[0])
            && !(new ReflectionMethod($callable[0], $callable[1]))->isStatic()
        ) {
            $callable[0] = $this->make($callable[0]);
        }
        return $callable;
    }

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
        return $reflection->newInstanceArgs($this->resolveArguments($constructor, $parameters));
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
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }
        if (isset($this->singletonBindings[$abstract])) {
            return $this->instances[$abstract] = $this->resolve($this->singletonBindings[$abstract], $parameters);
        }
        if (isset($this->bindings[$abstract])) {
            return $this->resolve($this->bindings[$abstract], $parameters);
        }
        if (class_exists($abstract)) {
            return $this->resolve($abstract, $parameters);
        }
        throw new NotFoundException("Target [$abstract] is not bound in the container.");
    }

    public function call(callable|array $callable, array $parameters = []): mixed
    {
        $callable = $this->resolveCallable($callable);
        $reflection = new ReflectionFunction(Closure::fromCallable($callable));
        return $callable(...$this->resolveArguments($reflection, $parameters));
    }

    public function get(string $id): mixed
    {
        return $this->make($id);
    }
}
