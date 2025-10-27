<?php

declare(strict_types=1);

namespace Tests\Unit\Fixtures;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

final class MinimalResponse implements ResponseInterface
{
    /** @var array<string, array<int, string>> */
    private array $headers = [];

    public function __construct(
        private int $statusCode,
        private string $reasonPhrase,
        private StreamInterface $body,
        private string $protocolVersion = '1.1'
    ) {
    }

    public function getProtocolVersion(): string
    {
        return $this->protocolVersion;
    }

    public function withProtocolVersion($version): ResponseInterface
    {
        $clone = clone $this;
        $clone->protocolVersion = (string) $version;
        return $clone;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function hasHeader($name): bool
    {
        return isset($this->headers[strtolower((string) $name)]);
    }

    public function getHeader($name): array
    {
        return $this->headers[strtolower((string) $name)] ?? [];
    }

    public function getHeaderLine($name): string
    {
        return implode(',', $this->getHeader($name));
    }

    public function withHeader($name, $value): ResponseInterface
    {
        $clone = clone $this;
        $clone->headers[strtolower((string) $name)] = (array) $value;
        return $clone;
    }

    public function withAddedHeader($name, $value): ResponseInterface
    {
        $clone = clone $this;
        $key = strtolower((string) $name);
        $clone->headers[$key] = array_merge($clone->headers[$key] ?? [], (array) $value);
        return $clone;
    }

    public function withoutHeader($name): ResponseInterface
    {
        $clone = clone $this;
        unset($clone->headers[strtolower((string) $name)]);
        return $clone;
    }

    public function getBody(): StreamInterface
    {
        return $this->body;
    }

    public function withBody(StreamInterface $body): ResponseInterface
    {
        $clone = clone $this;
        $clone->body = $body;
        return $clone;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function withStatus($code, $reasonPhrase = ''): ResponseInterface
    {
        $clone = clone $this;
        $clone->statusCode = (int) $code;
        $clone->reasonPhrase = (string) $reasonPhrase;
        return $clone;
    }

    public function getReasonPhrase(): string
    {
        return $this->reasonPhrase;
    }
}
