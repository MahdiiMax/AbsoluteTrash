<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;
use PDO;
use Psr\Http\Message\ServerRequestInterface;
use Trash\Database\Connection;
use Trash\Database\Migrator;
use Trash\Foundation\Application;
use Trash\Http\Message\Response;
use Trash\Http\Message\ServerRequest;
use Trash\Http\Message\Uri;
use Trash\Support\Hash;

abstract class TestCase extends BaseTestCase
{
    protected Connection $connection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = new Connection($this->sqlite());
        app()->instance(Connection::class, $this->connection);
        (new Migrator($this->connection))->fresh();
        $this->seed();
    }

    private function sqlite(): PDO
    {
        $pdo = new PDO('sqlite::memory:');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $pdo;
    }

    private function seed(): void
    {
        $this->connection->insert('users', [
            'name'       => 'Alice',
            'email'      => 'alice@example.com',
            'password'   => Hash::make('secret'),
            'active'     => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        $this->connection->insert('posts', [
            'user_id'    => 1,
            'title'      => 'First Post',
            'body'       => 'Hello world.',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    protected function get(string $uri, array $cookies = []): Response
    {
        return $this->dispatch('GET', $uri, cookies: $cookies);
    }

    protected function post(string $uri, array $data = [], array $cookies = []): Response
    {
        return $this->dispatch('POST', $uri, $data, $cookies);
    }

    protected function postForm(string $uri, array $data = [], array $cookies = []): Response
    {
        $this->get('/login', $cookies);
        $token = $this->csrfToken();
        return $this->dispatch('POST', $uri, [...$data, '_token' => $token], $this->sessionCookies($cookies));
    }

    private function dispatch(string $method,string $uri,array $data = [], array $cookies = [],array $files = []): Response {
        foreach ($cookies as $name => $value) {
            $_COOKIE[$name] = $value;
        }
        $request = new ServerRequest(
            $method,  new Uri($uri),  [],  null, '1.1', [],$cookies, [], $files, $data === [] ? null : $data
        );
        app()->instance(ServerRequestInterface::class, $request);
        app()->instance($request::class, $request);
        return app(Application::class)->handle($request);
    }

    protected function csrfToken(): string
    {
        return session()->token();
    }

    protected function sessionCookies(array $cookies = []): array
    {
        return [$this->sessionCookieName() => session()->getId()] + $cookies;
    }

    private function sessionCookieName(): string
    {
        return (string) config('session.cookie', 'absolute_trash_session');
    }

    protected function assertOk(Response $response): void
    {
        $this->assertSame(200, $response->getStatusCode());
    }

    protected function assertStatus(Response $response, int $status): void
    {
        $this->assertSame($status, $response->getStatusCode());
    }

    protected function assertRedirect(Response $response, string $path): void
    {
        $this->assertGreaterThanOrEqual(300, $response->getStatusCode());
        $this->assertLessThan(400, $response->getStatusCode());
        $this->assertSame($path, $response->getHeaderLine('Location'));
    }

    protected function assertSee(Response $response, string $needle): void
    {
        $this->assertStringContainsString($needle, (string) $response->getBody());
    }

    protected function assertNotSee(Response $response, string $needle): void
    {
        $this->assertStringNotContainsString($needle, (string) $response->getBody());
    }
}