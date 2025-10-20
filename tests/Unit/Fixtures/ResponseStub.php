<?php

declare(strict_types=1);

namespace Tests\Unit\Fixtures;

use BadMethodCallException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

final class ResponseStub implements ResponseInterface
{
    public function getProtocolVersion(): string
    {
        return '1.1';
    }

    public function withProtocolVersion($version): ResponseInterface
    {
        throw new BadMethodCallException('Not implemented in stub.');
    }

    public function getHeaders(): array
    {
        return [];
    }

    public function hasHeader($name): bool
    {
        return false;
    }

    public function getHeader($name): array
    {
        return [];
    }

    public function getHeaderLine($name): string
    {
        return '';
    }

    public function withHeader($name, $value): ResponseInterface
    {
        throw new BadMethodCallException('Not implemented in stub.');
    }

    public function withAddedHeader($name, $value): ResponseInterface
    {
        throw new BadMethodCallException('Not implemented in stub.');
    }

    public function withoutHeader($name): ResponseInterface
    {
        throw new BadMethodCallException('Not implemented in stub.');
    }

    public function getBody(): StreamInterface
    {
        throw new BadMethodCallException('Not implemented in stub.');
    }

    public function withBody(StreamInterface $body): ResponseInterface
    {
        throw new BadMethodCallException('Not implemented in stub.');
    }

    public function getStatusCode(): int
    {
        return 200;
    }

    public function withStatus($code, $reasonPhrase = ''): ResponseInterface
    {
        throw new BadMethodCallException('Not implemented in stub.');
    }

    public function getReasonPhrase(): string
    {
        return '';
    }
}
