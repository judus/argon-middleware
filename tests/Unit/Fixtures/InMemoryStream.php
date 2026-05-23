<?php

declare(strict_types=1);

namespace Tests\Unit\Fixtures;

use BadMethodCallException;
use Psr\Http\Message\StreamInterface;

final class InMemoryStream implements StreamInterface
{
    private int $position = 0;

    public function __construct(private string $contents = '')
    {
    }

    public function __toString(): string
    {
        return $this->contents;
    }

    #[\Override]
    public function close(): void
    {
        $this->detach();
    }

    #[\Override]
    public function detach()
    {
        $this->contents = '';
        $this->position = 0;
        return null;
    }

    #[\Override]
    public function getSize(): ?int
    {
        return strlen($this->contents);
    }

    #[\Override]
    public function tell(): int
    {
        return $this->position;
    }

    #[\Override]
    public function eof(): bool
    {
        return $this->position >= strlen($this->contents);
    }

    #[\Override]
    public function isSeekable(): bool
    {
        return true;
    }

    #[\Override]
    public function seek(int $offset, int $whence = SEEK_SET): void
    {
        $length = strlen($this->contents);

        switch ($whence) {
            case SEEK_SET:
                $target = $offset;
                break;
            case SEEK_CUR:
                $target = $this->position + $offset;
                break;
            case SEEK_END:
                $target = $length + $offset;
                break;
            default:
                throw new BadMethodCallException('Invalid whence value.');
        }

        if ($target < 0 || $target > $length) {
            throw new BadMethodCallException('Invalid seek position.');
        }

        $this->position = $target;
    }

    #[\Override]
    public function rewind(): void
    {
        $this->position = 0;
    }

    #[\Override]
    public function isWritable(): bool
    {
        return true;
    }

    #[\Override]
    public function write(string $string): int
    {
        $before = substr($this->contents, 0, $this->position);
        $after = substr($this->contents, $this->position + strlen($string));
        $this->contents = $before . $string . $after;
        $this->position += strlen($string);
        return strlen($string);
    }

    #[\Override]
    public function isReadable(): bool
    {
        return true;
    }

    #[\Override]
    public function read(int $length): string
    {
        $chunk = substr($this->contents, $this->position, $length);
        $this->position += strlen($chunk);
        return $chunk;
    }

    #[\Override]
    public function getContents(): string
    {
        $contents = substr($this->contents, $this->position);
        $this->position = strlen($this->contents);
        return $contents;
    }

    #[\Override]
    public function getMetadata(?string $key = null): mixed
    {
        $meta = ['uri' => 'in-memory'];
        return $key === null ? $meta : ($meta[$key] ?? null);
    }
}
