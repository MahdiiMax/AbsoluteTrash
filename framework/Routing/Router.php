<?php

declare(strict_types=1);

namespace Trash\Routing;

class Router
{
    public function __construct(private RouteCollection $routes) {}

    public function addRoute(
        array|string $method,
        string $path,
        callable|array|string $action,
        ?string $name = null,
        array $middleware = []
    ): Route {
        $route = new Route($method, $path, $action, $name, $middleware);
        $this->routes->addRoute($route);
        return $route;
    }

    public function get(string $path, callable|array|string $action): Route
    {
        return $this->addRoute('GET', $path, $action);
    }

    public function post(string $path, callable|array|string $action): Route
    {
        return $this->addRoute('POST', $path, $action);
    }

    public function put(string $path, callable|array|string $action): Route
    {
        return $this->addRoute('PUT', $path, $action);
    }

    public function patch(string $path, callable|array|string $action): Route
    {
        return $this->addRoute('PATCH', $path, $action);
    }

    public function delete(string $path, callable|array|string $action): Route
    {
        return $this->addRoute('DELETE', $path, $action);
    }

    public function options(string $path, callable|array|string $action): Route
    {
        return $this->addRoute('OPTIONS', $path, $action);
    }

    public function match(string $method, string $path): Route
    {
        return $this->routes->match($method, $path);
    }

    public function getRoutes(): array
    {
        return $this->routes->getRoutes();
    }
}
