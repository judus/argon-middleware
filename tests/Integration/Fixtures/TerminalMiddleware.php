<?php

declare(strict_types=1);

namespace Tests\Integration\Fixtures;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Tests\Unit\Fixtures\ResponseStub;

final class TerminalMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly LogCollector $collector,
        private readonly string $label,
        private readonly ResponseInterface $response = new ResponseStub()
    ) {
    }

    #[\Override]
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $this->collector->record($this->label);
        return $this->response;
    }
}
