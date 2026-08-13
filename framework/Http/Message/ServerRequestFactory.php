<?php

declare(strict_types=1);

namespace Trash\Http\Message;

use Psr\Http\Message\UploadedFileInterface;

final class ServerRequestFactory
{
    private static function createUri(array $server): Uri
    {
        $https = ($server['HTTPS'] ?? '') !== '' && strtolower((string) $server['HTTPS']) !== 'off';
        $scheme = $https ? 'https' : 'http';
        $uri = (new Uri())->withScheme($scheme);
        $authority = $server['HTTP_HOST'] ?? $server['SERVER_NAME'] ?? '';
         if ($authority !== '') {
        $port = null;
        if (preg_match('/^(.+):(\d+)$/', $authority, $m) === 1) {
            $authority = $m[1];
            $port = (int) $m[2];
        }
        $uri = $uri->withHost($authority);
        if ($port === null && isset($server['SERVER_PORT'])) {
            $port = (int) $server['SERVER_PORT'];
        }
        if ($port !== null && $port !== ($scheme === 'https' ? 443 : 80)) {
            $uri = $uri->withPort($port);
        }
    }   
        $requestUri = $server['REQUEST_URI'] ?? '/';
        $pos = strpos($requestUri, '?');
        $path = $pos === false ? $requestUri : substr($requestUri, 0, $pos);
        $query = $pos === false ? '' : substr($requestUri, $pos + 1);
        if ($path !== '') {
            $uri = $uri->withPath($path);
        }
        if ($query !== '') {
            $uri = $uri->withQuery($query);
        }
        return $uri;
    }

    private static function extractHeaders(array $server): array
    {
        $headers = [];
        foreach ($server as $key => $value) {
            if (!is_string($value) || $value === '') {
                continue;
            }
            if (str_starts_with($key, 'HTTP_')) {
                $headers[str_replace('_', '-', substr($key, 5))] = $value;
            } elseif (in_array($key, ['CONTENT_TYPE', 'CONTENT_LENGTH', 'CONTENT_MD5', 'AUTHORIZATION'], true)) {
                $headers[str_replace('_', '-', $key)] = $value;
            }
        }
        if (!isset($headers['Authorization']) && isset($server['REDIRECT_HTTP_AUTHORIZATION'])) {
            $headers['Authorization'] = $server['REDIRECT_HTTP_AUTHORIZATION'];
        }
        return $headers;
    }

    private static function createFromSpec(array $spec): UploadedFileInterface|array
    {
        if (is_array($spec['tmp_name'])) {
            $files = [];
            foreach (array_keys($spec['tmp_name']) as $k) {
                $files[$k] = self::createFromSpec([
                    'tmp_name' => $spec['tmp_name'][$k],
                    'size'     => $spec['size'][$k] ?? null,
                    'error'    => $spec['error'][$k] ?? null,
                    'name'     => $spec['name'][$k] ?? null,
                    'type'     => $spec['type'][$k] ?? null,
                ]);
            }
            return $files;
        }
        return new UploadedFile(
            $spec['tmp_name'],
            $spec['size'] ?? null,
            $spec['error'] ?? UPLOAD_ERR_OK,
            $spec['name'] ?? null,
            $spec['type'] ?? null
        );
    }

    private static function normalizeFiles(array $files): array
    {
        $normalized = [];
        foreach ($files as $key => $value) {
            if ($value instanceof UploadedFileInterface) {
                $normalized[$key] = $value;
            } elseif (is_array($value)) {
                $normalized[$key] = isset($value['tmp_name'])
                    ? self::createFromSpec($value)
                    : self::normalizeFiles($value);
            }
        }
        return $normalized;
    }

    public static function fromGlobals(
        ?array $server = null,
        ?array $query = null,
        ?array $body = null,
        ?array $cookies = null,
        ?array $files = null
    ): ServerRequest {
        $server ??= $_SERVER;
        $query ??= $_GET;
        $body ??= $_POST;
        $cookies ??= $_COOKIE;
        $files ??= $_FILES;
        $method = $server['REQUEST_METHOD'] ?? 'GET';
        $version = '1.1';
        if (isset($server['SERVER_PROTOCOL']) && preg_match('/^HTTP\/(\d+\.\d+)$/', $server['SERVER_PROTOCOL'], $m) === 1) {
            $version = $m[1];
        }
        return new ServerRequest(
            $method,
            self::createUri($server),
            self::extractHeaders($server),
            new Stream(fopen('php://input', 'r')),
            $version,
            $server,
            $cookies,
            $query,
            self::normalizeFiles($files),
            $body === [] ? null : $body
        );
    }
}
