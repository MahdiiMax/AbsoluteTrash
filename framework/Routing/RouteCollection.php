<?php

declare(strict_types=1);

namespace Trash\Routing;

use Trash\Routing\Exceptions\HttpNotFoundException;

class RouteCollection
{
    private array $routes = [];

    public function addRoute(Route $route): void
    {
        $this->routes[] = $route;
    }

    public function match(string $method, string $path): Route
    {
        foreach ($this->routes as $route) {
            if ($route->matches($method, $path) !== false) {
                return $route;
            }
        }
        throw new HttpNotFoundException(sprintf('No route found for %s %s', $method, $path));
    }

    public function getByName(string $name): ?Route
    {
        foreach ($this->routes as $route) {
            if ($route->getName() === $name) {
                return $route;
            }
        }
        return null;
    }

    public function getRoutes(): array
    {
        return $this->routes;
    }
}
