<?php

declare(strict_types=1);

namespace Trash\Routing;

use Override;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RuntimeException;
use Trash\Container\Container;
use Trash\Http\Message\Response;
use Trash\View\View;

class RouteHandler implements RequestHandlerInterface
{
    public function __construct(private Container $container) {}

    #[Override]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $route = $request->getAttribute(Route::class);
        if (!$route instanceof Route) {
            throw new RuntimeException('No route was matched for this request.');
        }
        $params = $request->getAttribute('routeParams', []);
        $result = $this->container->call($route->getAction(), $params);
        return match (true) {
            $result instanceof ResponseInterface => $result,
            $result instanceof View => Response::html($result->render()),
            is_string($result) => Response::html($result),
            is_array($result) => Response::json($result),
            default => throw new RuntimeException('Route action must return ResponseInterface, string, array, or View.'),
        };
    }
}
