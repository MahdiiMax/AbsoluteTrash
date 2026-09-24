<?php

declare(strict_types=1);

namespace Tests\Unit\Http;

use InvalidArgumentException;
use Tests\TestCase;
use Trash\Http\Message\Response;

class ResponseTest extends TestCase
{
    public function test_default_response(): void
    {
        $response = new Response();
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('OK', $response->getReasonPhrase());
        $this->assertSame('1.1', $response->getProtocolVersion());
        $this->assertSame('', (string) $response->getBody());
    }

    public function test_known_reason_phrases(): void
    {
        $this->assertSame('Not Found', (new Response(404))->getReasonPhrase());
        $this->assertSame('I\'m a Teapot', (new Response(418))->getReasonPhrase());
        $this->assertSame('Service Unavailable', (new Response(503))->getReasonPhrase());
    }

    public function test_unknown_status_has_empty_phrase(): void
    {
        $this->assertSame('', (new Response(599))->getReasonPhrase());
    }

    public function test_with_status_is_immutable(): void
    {
        $original = new Response(200);
        $changed = $original->withStatus(404);
        $this->assertSame(200, $original->getStatusCode());
        $this->assertSame(404, $changed->getStatusCode());
        $this->assertSame('Not Found', $changed->getReasonPhrase());
    }

    public function test_custom_reason_phrase(): void
    {
        $response = (new Response(200))->withStatus(200, 'Why Not');
        $this->assertSame('Why Not', $response->getReasonPhrase());
    }

    public function test_status_code_out_of_range_throws(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Response(600);
    }

    public function test_status_code_below_range_throws(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Response(99);
    }

    public function test_invalid_reason_phrase_throws(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Response(200, [], null, '1.1', "bad\nphrase");
    }

    public function test_json_helper(): void
    {
        $response = Response::json(['a' => 1, 'b' => [1, 2]], 201, ['X-Test' => '1']);
        $this->assertSame(201, $response->getStatusCode());
        $this->assertSame('application/json', $response->getHeaderLine('content-type'));
        $this->assertSame('1', $response->getHeaderLine('x-test'));
        $this->assertSame('{"a":1,"b":[1,2]}', (string) $response->getBody());
    }

    public function test_html_helper(): void
    {
        $response = Response::html('<p>hi</p>');
        $this->assertSame('text/html; charset=UTF-8', $response->getHeaderLine('content-type'));
        $this->assertSame('<p>hi</p>', (string) $response->getBody());
    }

    public function test_string_body_is_wrapped_in_stream(): void
    {
        $response = new Response(200, [], 'hello');
        $this->assertInstanceOf(\Psr\Http\Message\StreamInterface::class, $response->getBody());
        $this->assertSame('hello', (string) $response->getBody());
    }
}
