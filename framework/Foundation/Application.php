<?php

declare(strict_types=1);

namespace Trash\Foundation;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;
use Trash\Container\Container;
use Trash\Foundation\Facades\Facade;
use Trash\Http\Message\Response;
use Trash\Http\Message\ServerRequestFactory;
use Trash\Http\Middleware\Dispatcher;
use Trash\Http\Middleware\RouterMiddleware;
use Trash\Routing\Exceptions\HttpNotFoundException;
use Trash\Routing\RouteHandler;
use Trash\Routing\Router;

class Application extends Container
{
    private static ?Application $instance = null;

    public function __construct(private string $basePath)
    {
        self::$instance = $this;
        $this->instance(Application::class, $this);
        Facade::setFacadeApplication($this);
        $this->registerProviders();
    }

    private function registerProviders(): void
    {
        $providers = config('app.providers', []);
        $registered = [];
        foreach ($providers as $providerClass) {
            $provider = new $providerClass($this);
            $provider->register();
            $registered[] = $provider;
        }
        foreach ($registered as $provider) {
            $provider->boot();
        }
    }

    public static function getInstance(): Application
    {
        return self::$instance;
    }

    public function getBasePath(): string
    {
        return $this->basePath;
    }

    public function handle(?ServerRequestInterface $request = null): ResponseInterface
    {
        $request ??= ServerRequestFactory::fromGlobals();
        $this->instance(ServerRequestInterface::class, $request);
        $router = $this->make(Router::class);
        $global = array_map(fn(string $middleware) => $this->make($middleware), config('app.middleware', []));
        $pipeline = new Dispatcher(
            [...$global, new RouterMiddleware($router, $this)],
            new RouteHandler($this)
        );
        try {
            return $pipeline->handle($request);
        } catch (HttpNotFoundException) {
            return new Response(404, ['Content-Type' => 'text/plain'], 'Not Found');
        } catch (Throwable $e) {
            $code = $e->getCode();
            $status = ($code >= 100 && $code <= 599) ? $code : 500;
            return new Response($status, ['Content-Type' => 'text/plain'], $e->getMessage());
        }
    }
}
