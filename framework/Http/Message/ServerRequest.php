<?php

declare(strict_types=1);

namespace Trash\Http\Message;

use InvalidArgumentException;
use Override;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Message\UriInterface;

class ServerRequest extends Request implements ServerRequestInterface
{
    private array $serverParams, $cookieParams, $queryParams, $uploadedFiles, $attributes;
    private array|object|null $parsedBody;

    public function __construct(
        string $method = 'GET',
        ?UriInterface $uri = null,
        array $headers = [],
        mixed $body = null,
        string $version = '1.1',
        array $serverParams = [],
        array $cookieParams = [],
        array $queryParams = [],
        array $uploadedFiles = [],
        array|object|null $parsedBody = null,
        array $attributes = []
    ) {
        parent::__construct($method, $uri, $headers, $body, $version);
        $this->serverParams = $serverParams;
        $this->cookieParams = $cookieParams;
        $this->queryParams = $queryParams;
        $this->uploadedFiles = self::validateUploadedFiles($uploadedFiles);
        $this->parsedBody = $parsedBody;
        $this->attributes = $attributes;
    }

    private static function validateUploadedFiles(array $files): array
    {
        foreach ($files as $file) {
            if (is_array($file)) {
                self::validateUploadedFiles($file);
                continue;
            }
            if (!$file instanceof UploadedFileInterface) {
                throw new InvalidArgumentException('Uploaded files must be UploadedFileInterface instances.');
            }
        }
        return $files;
    }

    #[Override]
    public function getServerParams(): array
    {
        return $this->serverParams;
    }

    #[Override]
    public function getCookieParams(): array
    {
        return $this->cookieParams;
    }

    #[Override]
    public function withCookieParams(array $cookies): ServerRequestInterface
    {
        $clone = clone $this;
        $clone->cookieParams = $cookies;
        return $clone;
    }

    #[Override]
    public function getQueryParams(): array
    {
        return $this->queryParams;
    }

    #[Override]
    public function withQueryParams(array $query): ServerRequestInterface
    {
        $clone = clone $this;
        $clone->queryParams = $query;
        return $clone;
    }

    #[Override]
    public function getUploadedFiles(): array
    {
        return $this->uploadedFiles;
    }

    #[Override]
    public function withUploadedFiles(array $uploadedFiles): ServerRequestInterface
    {
        $clone = clone $this;
        $clone->uploadedFiles = self::validateUploadedFiles($uploadedFiles);
        return $clone;
    }

    #[Override]
    public function getParsedBody()
    {
        return $this->parsedBody;
    }

    #[Override]
    public function withParsedBody($data): ServerRequestInterface
    {
        if (!is_null($data) && !is_array($data) && !is_object($data)) {
            throw new InvalidArgumentException('Parsed body must be an array, object, or null.');
        }
        $clone = clone $this;
        $clone->parsedBody = $data;
        return $clone;
    }

    #[Override]
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    #[Override]
    public function getAttribute(string $name, $default = null)
    {
        return $this->attributes[$name] ?? $default;
    }

    #[Override]
    public function withAttribute(string $name, $value): ServerRequestInterface
    {
        $clone = clone $this;
        $clone->attributes[$name] = $value;
        return $clone;
    }

    #[Override]
    public function withoutAttribute(string $name): ServerRequestInterface
    {
        $clone = clone $this;
        unset($clone->attributes[$name]);
        return $clone;
    }
}
