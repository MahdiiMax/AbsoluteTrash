<?php

declare(strict_types=1);

namespace Trash\Http;

use Trash\Http\Message\Response;
use Trash\Routing\Router;

class RedirectResponse extends Response
{
    public function __construct(string $url, int $status = 302,)
    {
        parent::__construct($status, ["Location" => $url]);
    }

    public function to(string $url): static
    {
        return new static($url, $this->getStatusCode());
    }

    public function route(string $name): static
    {
        $route = app(Router::class)->getByName($name);
        return new static($route?->getPath() ?? '/', $this->getStatusCode());
    }

    public function with(string $key, mixed $value): static
    {
        session()?->flash($key, $value);
        return $this;
    }

    public function withErrors(array $errors): static
    {
        return $this->with('errors', $errors);
    }

    public function back(?string $fallback = '/'): static
    {
        $url = $_SERVER['HTTP_REFERER'] ?? $fallback;
        return new static($url, $this->getStatusCode());
    }
}
