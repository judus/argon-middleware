<?php

declare(strict_types=1);

namespace Tests\Unit\Fixtures;

use Maduser\Argon\Middleware\Exception\MiddlewareException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class TestMiddleware implements MiddlewareInterface
{
    #[\Override]
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        throw new MiddlewareException('This stub should never be called directly.');
    }
}
