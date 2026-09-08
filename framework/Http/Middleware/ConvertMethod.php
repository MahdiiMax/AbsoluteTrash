<?php

declare(strict_types=1);

namespace Trash\Http\Middleware;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Override;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ConvertMethod implements MiddlewareInterface
{
    #[Override]
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $method = strtoupper($request->getMethod());
        if ($method === 'POST') {
            $body = $request->getParsedBody();
            if (is_array($body) && isset($body['_method'])) {
                $spoofed = strtoupper((string) $body['_method']);
                if (in_array($spoofed, ['PUT', 'PATCH', 'DELETE'], true)) {
                    $request = $request->withMethod($spoofed);
                }
            }
        }
        return $handler->handle($request);
    }
}
