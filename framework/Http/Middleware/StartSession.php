<?php

declare(strict_types=1);

namespace Trash\Http\Middleware;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Override;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Trash\Session\SessionManager;
use Trash\Session\Store;

class StartSession implements MiddlewareInterface
{
    #[Override]
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $manager = app(SessionManager::class);
        $cookieName = config('session.cookie', 'absolute_trash_session');
        $lifetime = (int) config('session.lifetime', 120);
        $sessionId = $_COOKIE[$cookieName] ?? null;
        $store = $manager->store($sessionId);
        $store->start();
        app()->instance(Store::class, $store);
        $response = $handler->handle($request);
        $store->save();
        return $response->withHeader('Set-Cookie', sprintf(
            '%s=%s; Path=/; HttpOnly; SameSite=Lax; Max-Age=%d',
            $cookieName,
            $store->getId(),
            $lifetime * 60
        ));
    }
}