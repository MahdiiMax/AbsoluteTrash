<?php

declare(strict_types=1);

namespace Trash\Http\Middleware;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Override;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RuntimeException;

class Dispatcher implements RequestHandlerInterface
{
    public function __construct(
        private array $middleware,
        private RequestHandlerInterface $handler
    ) {}

    #[Override]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $middleware = array_shift($this->middleware);
        if ($middleware === null) {
            return $this->handler->handle($request);
        }
        if (!$middleware instanceof MiddlewareInterface) {
            throw new RuntimeException('Middleware must implement MiddlewareInterface.');
        }
        return $middleware->process($request, $this);
    }
}
