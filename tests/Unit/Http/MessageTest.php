<?php

declare(strict_types=1);

namespace Tests\Unit\Http;

use InvalidArgumentException;
use Psr\Http\Message\StreamInterface;
use Tests\TestCase;
use Trash\Http\Message\Response;
use Trash\Http\Message\Stream;
use Trash\Http\Message\Uri;

class MessageTest extends TestCase
{
    public function test_header_lookup_is_case_insensitive(): void
    {
        $message = new Response(200, ['Content-Type' => 'text/html']);
        $this->assertTrue($message->hasHeader('content-type'));
        $this->assertTrue($message->hasHeader('CONTENT-TYPE'));
        $this->assertSame(['text/html'], $message->getHeader('content-type'));
        $this->assertSame(['text/html'], $message->getHeader('Content-Type'));
    }

    public function test_get_header_line_joins_with_comma(): void
    {
        $message = new Response(200, ['X-H' => ['a', 'b']]);
        $this->assertSame('a, b', $message->getHeaderLine('x-h'));
    }

    public function test_missing_header_returns_empty(): void
    {
        $message = new Response();
        $this->assertFalse($message->hasHeader('missing'));
        $this->assertSame([], $message->getHeader('missing'));
        $this->assertSame('', $message->getHeaderLine('missing'));
    }

    public function test_with_header_replaces(): void
    {
        $message = new Response(200, ['X-Foo' => 'bar']);
        $changed = $message->withHeader('x-foo', 'baz');
        $this->assertSame('bar', $message->getHeaderLine('x-foo'));
        $this->assertSame('baz', $changed->getHeaderLine('x-foo'));
    }

    public function test_with_added_header_appends(): void
    {
        $message = new Response(200, ['X-Foo' => 'a']);
        $message = $message->withAddedHeader('x-foo', 'b')->withAddedHeader('X-Foo', 'c');
        $this->assertSame(['a', 'b', 'c'], $message->getHeader('x-foo'));
        $this->assertSame('a, b, c', $message->getHeaderLine('x-foo'));
    }

    public function test_without_header_removes(): void
    {
        $message = new Response(200, ['X-Foo' => 'bar', 'Y' => 'z']);
        $changed = $message->withoutHeader('x-foo');
        $this->assertFalse($changed->hasHeader('x-foo'));
        $this->assertTrue($changed->hasHeader('y'));
        $this->assertTrue($message->hasHeader('x-foo'));
    }

    public function test_invalid_header_name_throws(): void
    {
        $message = new Response();
        $this->expectException(InvalidArgumentException::class);
        $message->withHeader('Bad Name', 'x');
    }

    public function test_header_value_with_newline_throws(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Response(200, ['X' => "a\nb"]);
    }

    public function test_protocol_version(): void
    {
        $message = new Response();
        $this->assertSame('2.0', $message->withProtocolVersion('2.0')->getProtocolVersion());
        $this->assertSame('1.1', $message->getProtocolVersion());
    }

    public function test_invalid_protocol_version_throws(): void
    {
        $message = new Response();
        $this->expectException(InvalidArgumentException::class);
        $message->withProtocolVersion('2');
    }

    public function test_default_body_is_empty_stream(): void
    {
        $message = new Response();
        $this->assertInstanceOf(StreamInterface::class, $message->getBody());
        $this->assertSame('', (string) $message->getBody());
    }

    public function test_with_body_replaces_and_is_immutable(): void
    {
        $message = new Response();
        $changed = $message->withBody(Stream::create('new'));
        $this->assertSame('', (string) $message->getBody());
        $this->assertSame('new', (string) $changed->getBody());
    }

    public function test_request_derives_host_header_from_uri(): void
    {
        $request = new \Trash\Http\Message\Request('GET', new Uri('https://api.example.com/things'));
        $this->assertSame('api.example.com', $request->getHeaderLine('Host'));
        $this->assertSame('/things', $request->getRequestTarget());
    }

    public function test_request_target_includes_query(): void
    {
        $request = new \Trash\Http\Message\Request('GET', new Uri('https://e.com/p?q=1'));
        $this->assertSame('/p?q=1', $request->getRequestTarget());
    }

    public function test_request_target_defaults_to_slash(): void
    {
        $request = new \Trash\Http\Message\Request('GET', new Uri('https://e.com'));
        $this->assertSame('/', $request->getRequestTarget());
    }
}
