<?php

declare(strict_types=1);

namespace Tests\Integration\Fixtures;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class RecordingMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly LogCollector $collector,
        private readonly string $label
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $this->collector->record($this->label);
        return $handler->handle($request);
    }
}
