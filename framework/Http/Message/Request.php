<?php

declare(strict_types=1);

namespace Trash\Http\Message;

use InvalidArgumentException;
use Override;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

class Request extends Message implements RequestInterface
{
    private string $method = 'GET';
    private UriInterface $uri;
    private ?string $requestTarget = null;

    public function __construct(
        string $method = 'GET',
        ?UriInterface $uri = null,
        array $headers = [],
        mixed $body = null,
        string $version = '1.1'
    ) {
        parent::__construct($headers, $body instanceof StreamInterface ? $body : Stream::create($body), $version);
        $this->setMethod($method);
        $this->uri = $uri ?? new Uri();
        if ($this->getHeaderLine('Host') === '') {
            $this->updateHostFromUri();
        }
    }

    private function setMethod(string $method): void
    {
        if (preg_match('/^[!#$%&\'*+\-.^_`|~0-9A-Za-z]+$/', $method) !== 1) {
            throw new InvalidArgumentException('Invalid HTTP method: ' . $method);
        }
        $this->method = $method;
    }

    private function updateHostFromUri(): void
    {
        $host = $this->uri->getHost();
        if ($host === '') {
            return;
        }
        if (($port = $this->uri->getPort()) !== null) {
            $host .= ':' . $port;
        }
        $header = $this->headerNames['host'] ?? 'Host';
        $this->headerNames['host'] = $header;
        $this->headers = [$header => [$host]] + $this->headers;
    }

    #[Override]
    public function getRequestTarget(): string
    {
        if ($this->requestTarget !== null) {
            return $this->requestTarget;
        }
        $target = $this->uri->getPath();
        if ($target === '') {
            $target = '/';
        }
        if ($this->uri->getQuery() !== '') {
            $target .= '?' . $this->uri->getQuery();
        }
        return $target;
    }

    #[Override]
    public function withRequestTarget(string $requestTarget): RequestInterface
    {
        if (preg_match('/\s/', $requestTarget) === 1) {
            throw new InvalidArgumentException('Request target must not contain whitespace.');
        }
        $clone = clone $this;
        $clone->requestTarget = $requestTarget;
        return $clone;
    }

    #[Override]
    public function getMethod(): string
    {
        return $this->method;
    }

    #[Override]
    public function withMethod(string $method): RequestInterface
    {
        $clone = clone $this;
        $clone->setMethod($method);
        return $clone;
    }

    #[Override]
    public function getUri(): UriInterface
    {
        return $this->uri;
    }

    #[Override]
    public function withUri(UriInterface $uri, bool $preserveHost = false): RequestInterface
    {
        if ($uri === $this->uri) {
            return $this;
        }
        $clone = clone $this;
        $clone->uri = $uri;
        if (!$preserveHost || $clone->getHeaderLine('Host') === '') {
            $clone->updateHostFromUri();
        }
        return $clone;
    }
}
