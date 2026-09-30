<?php

declare(strict_types=1);

namespace Tests\Unit\Http;

use InvalidArgumentException;
use Tests\TestCase;
use Trash\Http\Message\Uri;

class UriTest extends TestCase
{
    public function test_parses_full_url(): void
    {
        $uri = new Uri('https://user:pass@example.com:8080/path?q=1#frag');
        $this->assertSame('https', $uri->getScheme());
        $this->assertSame('user:pass', $uri->getUserInfo());
        $this->assertSame('example.com', $uri->getHost());
        $this->assertSame(8080, $uri->getPort());
        $this->assertSame('/path', $uri->getPath());
        $this->assertSame('q=1', $uri->getQuery());
        $this->assertSame('frag', $uri->getFragment());
        $this->assertSame('user:pass@example.com:8080', $uri->getAuthority());
        $this->assertSame('https://user:pass@example.com:8080/path?q=1#frag', (string) $uri);
    }

    public function test_default_port_is_omitted_from_authority(): void
    {
        $uri = new Uri('http://example.com:80/');
        $this->assertSame(80, $uri->getPort());
        $this->assertSame('example.com', $uri->getAuthority());
        $this->assertSame('http://example.com/', (string) $uri);
    }

    public function test_file_scheme_without_host(): void
    {
        $this->assertSame('file:///tmp/x', (string) new Uri('file:///tmp/x'));
    }

    public function test_components_default_to_empty(): void
    {
        $uri = new Uri();
        $this->assertSame('', $uri->getScheme());
        $this->assertSame('', $uri->getHost());
        $this->assertNull($uri->getPort());
        $this->assertSame('', $uri->getPath());
        $this->assertSame('', (string) $uri);
    }

    public function test_immutability_of_with_methods(): void
    {
        $base = new Uri('http://example.com');
        $changed = $base->withPath('foo');
        $this->assertSame('http://example.com', (string) $base);
        $this->assertSame('http://example.com/foo', (string) $changed);
    }

    public function test_percent_encodes_components(): void
    {
        $uri = (new Uri('http://ex.com'))->withPath('/a b')->withQuery('q=x y')->withFragment('z w');
        $this->assertSame('/a%20b', $uri->getPath());
        $this->assertSame('q=x%20y', $uri->getQuery());
        $this->assertSame('z%20w', $uri->getFragment());
        $this->assertSame('http://ex.com/a%20b?q=x%20y#z%20w', (string) $uri);
    }

    public function test_host_is_lowercased(): void
    {
        $this->assertSame('example.com', (new Uri('https://EXAMPLE.COM/a'))->getHost());
    }

    public function test_with_scheme_lowercases(): void
    {
        $this->assertSame('ftp', (new Uri('http://ex.com'))->withScheme('FTP')->getScheme());
    }

    public function test_with_port_null_clears_port(): void
    {
        $uri = new Uri('http://example.com:8080');
        $this->assertNull($uri->withPort(null)->getPort());
    }

    public function test_invalid_components_throw(): void
    {
        $uri = new Uri('http://example.com');
        $this->expectException(InvalidArgumentException::class);
        $uri->withScheme('1bad');
        $uri->withPort(70000);
        $uri->withPort(0);
        $uri->withPath('/a?b');
        $uri->withQuery('a#b');
    }
}