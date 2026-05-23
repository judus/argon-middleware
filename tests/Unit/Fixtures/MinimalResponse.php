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

    #[\Override]
    public function getProtocolVersion(): string
    {
        return $this->protocolVersion;
    }

    #[\Override]
    public function withProtocolVersion(string $version): ResponseInterface
    {
        $clone = clone $this;
        $clone->protocolVersion = $version;
        return $clone;
    }

    #[\Override]
    public function getHeaders(): array
    {
        return $this->headers;
    }

    #[\Override]
    public function hasHeader(string $name): bool
    {
        return isset($this->headers[strtolower($name)]);
    }

    #[\Override]
    public function getHeader(string $name): array
    {
        return $this->headers[strtolower($name)] ?? [];
    }

    #[\Override]
    public function getHeaderLine(string $name): string
    {
        return implode(',', $this->getHeader($name));
    }

    #[\Override]
    public function withHeader(string $name, $value): ResponseInterface
    {
        $clone = clone $this;
        $clone->headers[strtolower($name)] = $this->normalizeHeaderValues($value);
        return $clone;
    }

    #[\Override]
    public function withAddedHeader(string $name, $value): ResponseInterface
    {
        $clone = clone $this;
        $key = strtolower($name);
        $clone->headers[$key] = array_merge($clone->headers[$key] ?? [], $this->normalizeHeaderValues($value));
        return $clone;
    }

    #[\Override]
    public function withoutHeader(string $name): ResponseInterface
    {
        $clone = clone $this;
        unset($clone->headers[strtolower($name)]);
        return $clone;
    }

    #[\Override]
    public function getBody(): StreamInterface
    {
        return $this->body;
    }

    #[\Override]
    public function withBody(StreamInterface $body): ResponseInterface
    {
        $clone = clone $this;
        $clone->body = $body;
        return $clone;
    }

    #[\Override]
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    #[\Override]
    public function withStatus(int $code, string $reasonPhrase = ''): ResponseInterface
    {
        $clone = clone $this;
        $clone->statusCode = $code;
        $clone->reasonPhrase = $reasonPhrase;
        return $clone;
    }

    #[\Override]
    public function getReasonPhrase(): string
    {
        return $this->reasonPhrase;
    }

    /**
     * @return list<string>
     */
    private function normalizeHeaderValues(mixed $value): array
    {
        if (is_array($value)) {
            return array_map(static fn(mixed $item): string => (string) $item, array_values($value));
        }

        return [(string) $value];
    }
}
