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

    public function close(): void
    {
        $this->detach();
    }

    public function detach()
    {
        $this->contents = '';
        $this->position = 0;
        return null;
    }

    public function getSize(): ?int
    {
        return strlen($this->contents);
    }

    public function tell(): int
    {
        return $this->position;
    }

    public function eof(): bool
    {
        return $this->position >= strlen($this->contents);
    }

    public function isSeekable(): bool
    {
        return true;
    }

    public function seek($offset, $whence = SEEK_SET): void
    {
        $length = strlen($this->contents);

        switch ($whence) {
            case SEEK_SET:
                $target = (int) $offset;
                break;
            case SEEK_CUR:
                $target = $this->position + (int) $offset;
                break;
            case SEEK_END:
                $target = $length + (int) $offset;
                break;
            default:
                throw new BadMethodCallException('Invalid whence value.');
        }

        if ($target < 0 || $target > $length) {
            throw new BadMethodCallException('Invalid seek position.');
        }

        $this->position = $target;
    }

    public function rewind(): void
    {
        $this->position = 0;
    }

    public function isWritable(): bool
    {
        return true;
    }

    public function write($string): int
    {
        $string = (string) $string;
        $before = substr($this->contents, 0, $this->position);
        $after = substr($this->contents, $this->position + strlen($string));
        $this->contents = $before . $string . $after;
        $this->position += strlen($string);
        return strlen($string);
    }

    public function isReadable(): bool
    {
        return true;
    }

    public function read($length): string
    {
        $length = (int) $length;
        $chunk = substr($this->contents, $this->position, $length);
        $this->position += strlen($chunk);
        return $chunk;
    }

    public function getContents(): string
    {
        $contents = substr($this->contents, $this->position);
        $this->position = strlen($this->contents);
        return $contents;
    }

    public function getMetadata($key = null)
    {
        $meta = ['uri' => 'in-memory'];
        return $key === null ? $meta : ($meta[$key] ?? null);
    }
}
