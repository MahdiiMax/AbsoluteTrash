<?php

declare(strict_types=1);

namespace Tests\Unit\Routing;

use Tests\TestCase;
use Trash\Routing\Exceptions\HttpNotFoundException;
use Trash\Routing\Route;
use Trash\Routing\RouteCollection;
use Trash\Routing\Router;

class RouterTest extends TestCase
{
    public function test_normalize_path(): void
    {
        $this->assertSame('/users', Route::normalizePath('users'));
        $this->assertSame('/users', Route::normalizePath('/users'));
    }

    public function test_verbs_add_routes(): void
    {
        $router = new Router(new RouteCollection());
        $this->assertSame(['GET'], $router->get('x', 'h')->getMethods());
        $this->assertSame(['POST'], $router->post('x', 'h')->getMethods());
        $this->assertSame(['DELETE'], $router->delete('x', 'h')->getMethods());
        $this->assertCount(3, $router->getRoutes());
    }

    public function test_matches_extracts_parameters(): void
    {
        $route = new Route('GET', 'users/{id}', 'h');
        $this->assertSame(['id' => '5'], $route->matches('GET', '/users/5'));
    }

    public function test_matches_optional_segment(): void
    {
        $route = new Route('GET', 'users/{id?}', 'h');
        $this->assertSame(['id' => ''], $route->matches('GET', '/users'));
        $this->assertSame(['id' => '7'], $route->matches('GET', '/users/7'));
    }

    public function test_matches_false_for_extra_segment_or_wrong_method(): void
    {
        $route = new Route('GET', 'users/{id}', 'h');
        $this->assertFalse($route->matches('GET', '/users/5/extra'));
        $this->assertFalse($route->matches('POST', '/users/5'));
    }

    public function test_router_match_and_get_by_name(): void
    {
        $router = new Router(new RouteCollection());
        $router->addRoute('GET', 'users', 'h', 'users.index', ['auth']);
        $this->assertSame('/users', $router->getByName('users.index')?->getPath());
        $this->assertInstanceOf(Route::class, $router->match('GET', '/users'));
        $this->assertNull($router->getByName('missing'));
    }

    public function test_http_not_found_when_no_match(): void
    {
        $router = new Router(new RouteCollection());
        $this->expectException(HttpNotFoundException::class);
        $router->match('GET', '/nope');
    }

    public function test_name_and_middleware_chaining(): void
    {
        $route = new Route('GET', 'posts', 'h');
        $route->name('posts.index')->middleware(['auth']);
        $this->assertSame('posts.index', $route->getName());
        $this->assertSame(['auth'], $route->getMiddleware());
    }
}