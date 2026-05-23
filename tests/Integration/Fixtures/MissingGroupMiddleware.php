<?php

declare(strict_types=1);

namespace Tests\Integration\Fixtures;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class MissingGroupMiddleware implements MiddlewareInterface
{
    private readonly RecordingMiddleware $delegate;

    public function __construct(LogCollector $collector)
    {
        $this->delegate = new RecordingMiddleware($collector, 'missing-group');
    }

    #[\Override]
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return $this->delegate->process($request, $handler);
    }
}
