<?php

declare(strict_types=1);

namespace Trash\Http\Message;

use InvalidArgumentException;
use Override;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;

abstract class Message implements MessageInterface
{
    protected string $protocolVersion = '1.1';
    protected array $headers = [];
    protected array $headerNames = [];
    protected StreamInterface $body;

    public function __construct(array $headers = [], ?StreamInterface $body = null, string $version = '1.1')
    {
        $this->protocolVersion = $version;
        foreach ($headers as $name => $value) {
            $this->addHeader($name, self::normalizeValue($value));
        }
        $this->body = $body ?? Stream::create('');
    }

    private function addHeader(string $name, array $values): void
    {
        self::validateName($name);
        $normalized = self::normalizeKey($name);
        if (isset($this->headerNames[$normalized])) {
            unset($this->headers[$this->headerNames[$normalized]]);
        }
        $this->headerNames[$normalized] = $name;
        $this->headers[$name] = $values;
    }

    private static function normalizeKey(string $name): string
    {
        return strtolower($name);
    }

    private static function validateName(string $name): void
    {
        if (preg_match('/^[!#$%&\'*+\-.^_`|~0-9A-Za-z]+$/', $name) !== 1) {
            throw new InvalidArgumentException('Invalid header name: ' . $name);
        }
    }

    private static function normalizeValue(mixed $value): array
    {
        $values = is_array($value) ? $value : [$value];
        return array_map(
            static function (mixed $v): string {
                if (!is_scalar($v)) {
                    throw new InvalidArgumentException('Header values must be scalar.');
                }
                $v = trim((string)$v);
                if (str_contains($v, "\r") || str_contains($v, "\n")) {
                    throw new InvalidArgumentException('Header value cannot contain newlines.');
                }
                return $v;
            },
            $values
        );
    }

    #[Override]
    public function getProtocolVersion(): string
    {
        return $this->protocolVersion;
    }

    #[Override]
    public function withProtocolVersion(string $version): MessageInterface
    {
        if (preg_match('/^\d+\.\d+$/', $version) !== 1) {
            throw new InvalidArgumentException('Invalid protocol version: ' . $version);
        }
        $clone = clone $this;
        $clone->protocolVersion = $version;
        return $clone;
    }

    #[Override]
    public function getHeaders(): array
    {
        return $this->headers;
    }

    #[Override]
    public function hasHeader(string $name): bool
    {
        return isset($this->headerNames[self::normalizeKey($name)]);
    }

    #[Override]
    public function getHeader(string $name): array
    {
        $key = self::normalizeKey($name);
        return isset($this->headerNames[$key]) ? $this->headers[$this->headerNames[$key]] : [];
    }

    #[Override]
    public function getHeaderLine(string $name): string
    {
        return implode(', ', $this->getHeader($name));
    }

    #[Override]
    public function withHeader(string $name, $value): MessageInterface
    {
        self::validateName($name);
        $values = self::normalizeValue($value);
        $clone = clone $this;
        $normalized = self::normalizeKey($name);
        if (isset($this->headerNames[$normalized])) {
            $clone->headers[$this->headerNames[$normalized]] = $values;
        } else {
            $clone->headerNames[$normalized] = $name;
            $clone->headers[$name] = $values;
        }
        return $clone;
    }

    #[Override]
    public function withAddedHeader(string $name, $value): MessageInterface
    {
        self::validateName($name);
        $values = self::normalizeValue($value);
        $clone = clone $this;
        $normalized = self::normalizeKey($name);
        if (isset($this->headerNames[$normalized])) {
            $header = $this->headerNames[$normalized];
            $clone->headers[$header] = array_merge($clone->headers[$header], $values);
        } else {
            $clone->headerNames[$normalized] = $name;
            $clone->headers[$name] = $values;
        }
        return $clone;
    }

    #[Override]
    public function withoutHeader(string $name): MessageInterface
    {
        $clone = clone $this;
        $normalized = self::normalizeKey($name);
        if (isset($this->headerNames[$normalized])) {
            $header = $this->headerNames[$normalized];
            unset($clone->headers[$header], $clone->headerNames[$normalized]);
        }
        return $clone;
    }

    #[Override]
    public function getBody(): StreamInterface
    {
        return $this->body;
    }

    #[Override]
    public function withBody(StreamInterface $body): MessageInterface
    {
        $clone = clone $this;
        $clone->body = $body;
        return $clone;
    }
}
