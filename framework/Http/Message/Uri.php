<?php

declare(strict_types=1);

namespace Trash\Http\Message;

use InvalidArgumentException;
use Override;
use Psr\Http\Message\UriInterface;

class Uri implements UriInterface
{
    private const DEFAULT_PORTS = [
        'http' => 80,
        'https' => 443,
        'ws' => 80,
        'wss' => 443,
    ];
    private const UNRESERVED = 'A-Za-z0-9\-._~';
    private const SUB_DELIMS = "!\$&'()*+,;=";
    private ?string $scheme = null;
    private ?string $userInfo = null;
    private ?string $host = null;
    private ?int $port = null;
    private string $path = '';
    private string $query = '';
    private string $fragment = '';

    public function __construct(string $uri = '')
    {
        $parts = parse_url($uri);
        if ($parts === false) {
            throw new InvalidArgumentException('Invalid URI.');
        }
        $this->scheme = isset($parts['scheme']) ? strtolower($parts['scheme']) : null;
        if (isset($parts['user'])) {
            $userInfo = $parts['user'] . (isset($parts['pass']) ? ':' . $parts['pass'] : '');
            $this->userInfo = self::encode($userInfo, ':');
        }
        if (isset($parts['host'])) {
            $this->host = self::filterHost($parts['host']);
        }
        if (isset($parts['port'])) {
            $this->port = self::filterPort($parts['port']);
        }
        $this->path = self::encode($parts['path'] ?? '', ':@/');
        $this->query = self::encode($parts['query'] ?? '', ':@/?');
        $this->fragment = self::encode($parts['fragment'] ?? '', ':@/?');
    }

    private static function encode(string $component, string $extra = ''): string
    {
        if ($component === '') {
            return '';
        }
        $class = self::UNRESERVED . self::SUB_DELIMS . '%' . str_replace('/', '\/', $extra);
        $pattern = '/(?:[^' . $class . '%' . ']++|%(?![0-9A-Fa-f]{2}))/';
        return preg_replace_callback(
            $pattern,
            static fn(array $m): string => rawurlencode($m[0]),
            $component
        );
    }

    private static function filterHost(string $host): string
    {
        $host = strtolower($host);
        if (preg_match('/^\[?(?:[a-z0-9\-._~!$&\'()*+,;=:]|%[0-9a-f]{2})+\]?$/i', $host) !== 1) {
            throw new InvalidArgumentException('Invalid host: ' . $host);
        }
        return $host;
    }

    private static function filterPort(int $port): int
    {
        if ($port < 1 || $port > 65535) {
            throw new InvalidArgumentException('Invalid port: ' . $port);
        }
        return $port;
    }

    #[Override]
    public function getScheme(): string
    {
        return $this->scheme ?? '';
    }

    #[Override]
    public function getAuthority(): string
    {
        $authority = '';
        if ($this->userInfo !== null) {
            $authority .= $this->userInfo . '@';
        }
        if ($this->host !== null) {
            $authority .= $this->host;
            $default = $this->scheme !== null ? (self::DEFAULT_PORTS[$this->scheme] ?? null) : null;
            if ($this->port !== null && $this->port !== $default) {
                $authority .= ':' . $this->port;
            }
        }
        return $authority;
    }

    #[Override]
    public function getUserInfo(): string
    {
        return $this->userInfo ?? '';
    }

    #[Override]
    public function getHost(): string
    {
        return $this->host ?? '';
    }

    #[Override]
    public function getPort(): ?int
    {
        return $this->port;
    }

    #[Override]
    public function getPath(): string
    {
        return $this->path;
    }

    #[Override]
    public function getQuery(): string
    {
        return $this->query;
    }

    #[Override]
    public function getFragment(): string
    {
        return $this->fragment;
    }

    #[Override]
    public function withScheme(string $scheme): UriInterface
    {
        $scheme = strtolower($scheme);
        if ($scheme !== '' && preg_match('/^[a-z][a-z0-9+.\-]*$/', $scheme) !== 1) {
            throw new InvalidArgumentException('Invalid scheme: ' . $scheme);
        }
        $clone = clone $this;
        $clone->scheme = $scheme === '' ? null : $scheme;
        return $clone;
    }

    #[Override]
    public function withUserInfo(string $user, ?string $password = null): UriInterface
    {
        $userInfo = $user . ($password !== null && $password !== '' ? ':' . $password : '');
        $clone = clone $this;
        $clone->userInfo = $user === '' ? null : self::encode($userInfo, ':');
        return $clone;
    }

    #[Override]
    public function withHost(string $host): UriInterface
    {
        $clone = clone $this;
        $clone->host = $host === '' ? null : self::filterHost($host);
        return $clone;
    }

    #[Override]
    public function withPort(?int $port): UriInterface
    {
        $clone = clone $this;
        $clone->port = $port !== null ? self::filterPort($port) : null;
        return $clone;
    }

    #[Override]
    public function withPath(string $path): UriInterface
    {
        if (strpbrk($path, '?#') !== false) {
            throw new InvalidArgumentException('Path must not contain "?" or "#".');
        }
        $clone = clone $this;
        $clone->path = self::encode($path, ':@/');
        return $clone;
    }

    #[Override]
    public function withQuery(string $query): UriInterface
    {
        if (str_contains($query, '#')) {
            throw new InvalidArgumentException('Query must not contain "#".');
        }
        $clone = clone $this;
        $clone->query = self::encode($query, ':@/?');
        return $clone;
    }

    #[Override]
    public function withFragment(string $fragment): UriInterface
    {
        $clone = clone $this;
        $clone->fragment = self::encode($fragment, ':@/?');
        return $clone;
    }

    #[Override]
    public function __toString(): string
    {
        $authority = $this->getAuthority();
        $uri = $this->scheme !== null ? $this->scheme . ':' : '';
        if ($authority !== '' || $this->scheme === 'file') {
            $uri .= '//' . $authority;
        }
        $path = $this->path;
        if ($path !== '') {
            if ($authority !== '' && !str_starts_with($path, '/')) {
                $path = '/' . $path;
            } elseif ($authority === '' && preg_match('#^//+#', $path)) {
                $path = '/' . ltrim($path, '/');
            }
        }
        $uri .= $path;
        if ($this->query !== '') {
            $uri .= '?' . $this->query;
        }
        if ($this->fragment !== '') {
            $uri .= '#' . $this->fragment;
        }
        return $uri;
    }
}
