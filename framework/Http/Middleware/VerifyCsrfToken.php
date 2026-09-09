<?php

declare(strict_types=1);

namespace Trash\Http\Middleware;

use Override;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Trash\Session\Store;

class VerifyCsrfToken implements MiddlewareInterface
{
    protected array $except = [];

    #[Override]
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($this->isReading($request) || $this->inExcept($request)) {
            return $handler->handle($request);
        }
        $token = $this->getTokenFromRequest($request);
        if (!is_string($token) || !hash_equals(app(Store::class)->token(), $token)) {
            abort(419, 'Page Expired.');
        }
        return $handler->handle($request);
    }

    private function isReading(ServerRequestInterface $request): bool
    {
        return in_array($request->getMethod(), ['GET', 'HEAD', 'OPTIONS'], true);
    }

    private function inExcept(ServerRequestInterface $request): bool
    {
        return in_array($request->getUri()->getPath(), $this->except, true);
    }

    private function getTokenFromRequest(ServerRequestInterface $request): ?string
    {
        $body = $request->getParsedBody();
        if (is_array($body) && isset($body['_token']) && is_string($body['_token'])) {
            return $body['_token'];
        }
        return null;
    }
}
