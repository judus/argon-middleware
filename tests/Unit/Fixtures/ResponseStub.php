<?php

declare(strict_types=1);

namespace Tests\Unit\Fixtures;

use BadMethodCallException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

final class ResponseStub implements ResponseInterface
{
    #[\Override]
    public function getProtocolVersion(): string
    {
        return '1.1';
    }

    #[\Override]
    public function withProtocolVersion($version): ResponseInterface
    {
        throw new BadMethodCallException('Not implemented in stub.');
    }

    #[\Override]
    public function getHeaders(): array
    {
        return [];
    }

    #[\Override]
    public function hasHeader($name): bool
    {
        return false;
    }

    #[\Override]
    public function getHeader($name): array
    {
        return [];
    }

    #[\Override]
    public function getHeaderLine($name): string
    {
        return '';
    }

    #[\Override]
    public function withHeader($name, $value): ResponseInterface
    {
        throw new BadMethodCallException('Not implemented in stub.');
    }

    #[\Override]
    public function withAddedHeader($name, $value): ResponseInterface
    {
        throw new BadMethodCallException('Not implemented in stub.');
    }

    #[\Override]
    public function withoutHeader($name): ResponseInterface
    {
        throw new BadMethodCallException('Not implemented in stub.');
    }

    #[\Override]
    public function getBody(): StreamInterface
    {
        throw new BadMethodCallException('Not implemented in stub.');
    }

    #[\Override]
    public function withBody(StreamInterface $body): ResponseInterface
    {
        throw new BadMethodCallException('Not implemented in stub.');
    }

    #[\Override]
    public function getStatusCode(): int
    {
        return 200;
    }

    #[\Override]
    public function withStatus($code, $reasonPhrase = ''): ResponseInterface
    {
        throw new BadMethodCallException('Not implemented in stub.');
    }

    #[\Override]
    public function getReasonPhrase(): string
    {
        return '';
    }
}
