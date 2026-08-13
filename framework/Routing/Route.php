<?php

declare(strict_types=1);

namespace Trash\Routing;

use Closure;

class Route
{
    private ?string $compiledPattern = null;
    private array $compiledParams = [];

    public function __construct(
        public array|string $methods,
        public string $path,
        public array|string|Closure $action,
        public ?string $name = null,
        public array $middleware = []
    ) {
        $this->methods = is_array($methods) ? array_map('strtoupper', $methods) : [strtoupper($methods)];
        $this->path = self::normalizePath($path);
    }

    public static function normalizePath(string $path): string
    {
        return '/' . trim($path, '/');
    }

    public function name(?string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function middleware(array|string $middleware): static
    {
        $this->middleware = array_merge($this->middleware, is_array($middleware) ? $middleware : [$middleware]);
        return $this;
    }

    public function getMethods(): array
    {
        return $this->methods;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getAction(): callable|array|string
    {
        return $this->action;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getMiddleware(): array
    {
        return $this->middleware;
    }

    public function matches(string $method, string $path): array|false
    {
        if (!in_array(strtoupper($method), $this->methods, true) || preg_match($this->compiledPattern(),  self::normalizePath($path), $m) !== 1) {
            return false;
        }
        $params = [];
        foreach ($this->compiledParams as $param) {
            $params[$param] = $m[$param] ?? '';
        }
        return $params;
    }

    private function compiledPattern(): string
    {
        if ($this->compiledPattern !== null) {
            return $this->compiledPattern;
        }
        $segments = explode('/', trim($this->path, '/'));
        $regex = '';
        $lastIndex = array_key_last($segments);
        foreach ($segments as $index => $segment) {
            if ($segment === '') {
                continue;
            }
            if (preg_match('/^\{([a-zA-Z0-9_]+)(\?)?\}$/', $segment, $m)) {
                $name = $m[1];
                $this->compiledParams[] = $name;
                if (isset($m[2]) && $index === $lastIndex) {
                    $regex .= '(?:/(?P<' . $name . '>[^/]+))?';
                } else {
                    $regex .= '/(?P<' . $name . '>[^/]+)';
                }
            } else {
                $regex .= '/' . preg_quote($segment, '#');
            }
        }
        $this->compiledPattern = '#^' . ($regex === '' ? '/' : $regex) . '$#';
        return $this->compiledPattern;
    }
}
