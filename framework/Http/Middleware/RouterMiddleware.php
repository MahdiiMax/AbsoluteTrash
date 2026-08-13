<?php

declare(strict_types=1);

namespace Trash\Http\Middleware;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Override;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Trash\Container\Container;
use Trash\Routing\Route;
use Trash\Routing\Router;

class RouterMiddleware implements MiddlewareInterface
{
    public function __construct(
        private Router $router,
        private Container $container
    ) {}

    #[Override]
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $method = $request->getMethod();
        $path = $request->getUri()->getPath();
        $route = $this->router->match($method, $path);
        $request = $request
            ->withAttribute(Route::class, $route)
            ->withAttribute('routeParams', $route->matches($method, $path));
        $middleware = array_map(fn(string $class) => $this->container->make($class), $route->getMiddleware());
        return (new Dispatcher($middleware, $handler))->handle($request);
    }
}
