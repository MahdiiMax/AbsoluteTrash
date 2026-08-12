<?php

declare(strict_types=1);

namespace Trash\Http\Message;

use InvalidArgumentException;
use Override;
use Psr\Http\Message\StreamInterface;
use RuntimeException;
use Throwable;

class Stream implements StreamInterface
{
    private $stream;
    private ?int $size = null;
    private bool $seekable, $readable, $writable;

    public function __construct($stream)
    {
        if (!is_resource($stream)) {
            throw new InvalidArgumentException('Stream must be a valid resource.');
        }
        $this->stream = $stream;
        $meta = stream_get_meta_data($stream);
        $this->seekable = $meta['seekable'];
        $this->readable = (bool) preg_match('/[r+]/', $meta['mode']);
        $this->writable = (bool) preg_match('/[waxc+]/', $meta['mode']);
    }

    private function resetAttr(): void
    {
        $this->stream = null;
        $this->size = null;
        $this->seekable = false;
        $this->readable = false;
        $this->writable = false;
    }

    public static function create(mixed $body): Stream
    {
        if (is_resource($body)) {
            return new self($body);
        }
        $stream = fopen('php://temp', 'r+');
        if ($body !== null) {
            fwrite($stream, (string)$body);
            rewind($stream);
        }
        return new self($stream);
    }

    #[Override]
    public function __toString(): string
    {
        try {
            $this->rewind();
            return $this->getContents();
        } catch (Throwable $e) {
            return '';
        }
    }

    #[Override]
    public function close(): void
    {
        if (is_resource($this->stream)) {
            fclose($this->stream);
        }
        $this->resetAttr();
    }

    #[Override]
    public function detach()
    {
        if (!is_resource($this->stream)) {
            return null;
        }
        $stream = $this->stream;
        $this->resetAttr();
        return $stream;
    }

    #[Override]
    public function getSize(): ?int
    {
        if ($this->size !== null) {
            return $this->size;
        }
        if (!is_resource($this->stream)) {
            return null;
        }
        $stats = fstat($this->stream);
        return $this->size = $stats['size'] ?? null;
    }

    #[Override]
    public function tell(): int
    {
        if (!is_resource($this->stream)) {
            throw new RuntimeException('Stream is closed or detached.');
        }
        return ftell($this->stream);
    }

    #[Override]
    public function eof(): bool
    {
        return is_resource($this->stream) && feof($this->stream);
    }

    #[Override]
    public function isSeekable(): bool
    {
        return $this->seekable;
    }

    #[Override]
    public function seek(int $offset, int $whence = SEEK_SET): void
    {
        if (!is_resource($this->stream) || !$this->seekable) {
            throw new RuntimeException('Stream is not seekable.');
        }
        if (fseek($this->stream, $offset, $whence) === -1) {
            throw new RuntimeException('Unable to seek to the requested position.');
        }
    }

    #[Override]
    public function rewind(): void
    {
        $this->seek(0);
    }

    #[Override]
    public function isWritable(): bool
    {
        return $this->writable;
    }

    #[Override]
    public function write(string $string): int
    {
        if (!is_resource($this->stream) || !$this->writable) {
            throw new RuntimeException('Stream is not writable.');
        }
        $this->size = null;
        return fwrite($this->stream, $string);
    }

    #[Override]
    public function isReadable(): bool
    {
        return $this->readable;
    }

    #[Override]
    public function read(int $length): string
    {
        if (!is_resource($this->stream) || !$this->readable) {
            throw new RuntimeException('Stream is not readable.');
        }
        return fread($this->stream, $length);
    }

    #[Override]
    public function getContents(): string
    {
        if (!is_resource($this->stream) || !$this->readable) {
            throw new RuntimeException('Stream is not readable.');
        }
        return stream_get_contents($this->stream);
    }

    #[Override]
    public function getMetadata(?string $key = null)
    {
        if (!is_resource($this->stream)) {
            return $key === null ? [] : null;
        }
        $meta = stream_get_meta_data($this->stream);
        return $key === null ? $meta : ($meta[$key] ?? null);
    }
}
