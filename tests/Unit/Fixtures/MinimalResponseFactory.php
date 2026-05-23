<?php

declare(strict_types=1);

namespace Tests\Unit\Fixtures;

use BadMethodCallException;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;

final class MinimalResponseFactory implements ResponseFactoryInterface, StreamFactoryInterface
{
    #[\Override]
    public function createResponse(int $code = 200, string $reasonPhrase = ''): ResponseInterface
    {
        return new MinimalResponse($code, $reasonPhrase, new InMemoryStream());
    }

    #[\Override]
    public function createStream(string $content = ''): StreamInterface
    {
        return new InMemoryStream($content);
    }

    #[\Override]
    public function createStreamFromFile(string $filename, string $mode = 'r'): StreamInterface
    {
        throw new BadMethodCallException('Not implemented in stub.');
    }

    #[\Override]
    public function createStreamFromResource($resource): StreamInterface
    {
        throw new BadMethodCallException('Not implemented in stub.');
    }
}
