<?php

declare(strict_types=1);

namespace Tests\Unit\Http;

use InvalidArgumentException;
use RuntimeException;
use Tests\TestCase;
use Trash\Http\Message\Stream;

class StreamTest extends TestCase
{
    public function test_create_from_string(): void
    {
        $stream = Stream::create('hello world');
        $this->assertTrue($stream->isSeekable());
        $this->assertTrue($stream->isReadable());
        $this->assertTrue($stream->isWritable());
        $this->assertSame(11, $stream->getSize());
        $this->assertSame('hello world', (string) $stream);
    }

    public function test_read_advances_position(): void
    {
        $stream = Stream::create('hello world');
        $this->assertSame('hello', $stream->read(5));
        $this->assertSame(5, $stream->tell());
        $this->assertSame(' worl', $stream->read(5));
    }

    public function test_seek_and_rewind(): void
    {
        $stream = Stream::create('0123456789');
        $stream->seek(5);
        $this->assertSame('56789', $stream->getContents());
        $stream->rewind();
        $this->assertSame(0, $stream->tell());
    }

    public function test_write_appends_and_size_updates(): void
    {
        $stream = Stream::create('ab');
        $written = $stream->write('cd');
        $this->assertSame(2, $written);
        $stream->rewind();
        $this->assertSame('cd', (string) $stream);
        $this->assertSame(2, $stream->getSize());
    }

    public function test_write_appends_at_end_when_seeked(): void
    {
        $stream = Stream::create('ab');
        $stream->seek(0, SEEK_END);
        $stream->write('cd');
        $this->assertSame('abcd', (string) $stream);
    }

    public function test_metadata(): void
    {
        $stream = Stream::create('x');
        $this->assertIsArray($stream->getMetadata());
        $this->assertSame('php://temp', $stream->getMetadata('uri'));
    }

    public function test_detach_returns_resource(): void
    {
        $stream = Stream::create('x');
        $resource = $stream->detach();
        $this->assertIsResource($resource);
        $this->assertNull($stream->getSize());
        $this->assertFalse($stream->isReadable());
    }

    public function test_operations_after_close_throw(): void
    {
        $stream = Stream::create('x');
        $stream->close();
        $this->assertFalse($stream->isReadable());
        $this->assertNull($stream->getSize());
        $this->expectException(RuntimeException::class);
        $stream->read(1);
    }

    public function test_invalid_constructor_throws(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Stream('not a resource');
    }
}
