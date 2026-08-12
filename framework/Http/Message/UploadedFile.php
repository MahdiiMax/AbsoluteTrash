<?php

declare(strict_types=1);

namespace Trash\Http\Message;

use Override;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use RuntimeException;

class UploadedFile implements UploadedFileInterface
{
    private StreamInterface|string $file;
    private ?int $size;
    private int $error;
    private ?string $clientFilename;
    private ?string $clientMediaType;
    private ?StreamInterface $stream = null;
    private bool $moved = false;

    public function __construct(
        StreamInterface|string $file,
        ?int $size = null,
        int $error = UPLOAD_ERR_OK,
        ?string $clientFilename = null,
        ?string $clientMediaType = null
    ) {
        $this->file = $file;
        $this->size = $size;
        $this->error = $error;
        $this->clientFilename = $clientFilename;
        $this->clientMediaType = $clientMediaType;
    }

    #[Override]
    public function getStream(): StreamInterface
    {
        if ($this->moved) {
            throw new RuntimeException('Cannot retrieve a stream after the file has been moved.');
        }
        if ($this->error !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Cannot retrieve a stream for a file with an upload error.');
        }
        if ($this->stream !== null) {
            return $this->stream;
        }
        if ($this->file instanceof StreamInterface) {
            return $this->stream = $this->file;
        }
        $handle = fopen($this->file, 'r');
        if ($handle === false) {
            throw new RuntimeException('Unable to open the uploaded file stream.');
        }
        return $this->stream = new Stream($handle);
    }

    #[Override]
    public function moveTo(string $targetPath): void
    {
        if ($this->moved) {
            throw new RuntimeException('Cannot move the file more than once.');
        }
        if ($this->error !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Cannot move a file with an upload error.');
        }
        if ($this->file instanceof StreamInterface) {
            $stream = $this->file;
            $stream->rewind();
            $dest = fopen($targetPath, 'wb');
            if ($dest === false) {
                throw new RuntimeException('Unable to open the target path.');
            }
            $source = $stream->detach();
            if (is_resource($source)) {
                stream_copy_to_stream($source, $dest);
            }
            fclose($dest);
            $this->moved = true;
            return;
        }
        $isCli = in_array(PHP_SAPI, ['cli', 'phpdbg'], true);
        if (!$isCli && !is_uploaded_file($this->file)) {
            throw new RuntimeException('The uploaded file was not produced by a POST request.');
        }
        $moved = $isCli && !is_uploaded_file($this->file)
            ? rename($this->file, $targetPath)
            : move_uploaded_file($this->file, $targetPath);
        if ($moved === false) {
            throw new RuntimeException('Unable to move the uploaded file.');
        }
        $this->moved = true;
    }

    #[Override]
    public function getSize(): ?int
    {
        return $this->size;
    }

    #[Override]
    public function getError(): int
    {
        return $this->error;
    }

    #[Override]
    public function getClientFilename(): ?string
    {
        return $this->clientFilename;
    }

    #[Override]
    public function getClientMediaType(): ?string
    {
        return $this->clientMediaType;
    }
}
