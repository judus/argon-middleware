<?php

declare(strict_types=1);

namespace Tests\Integration\Fixtures;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Tests\Unit\Fixtures\ResponseStub;

final class SettableRequestHandler implements RequestHandlerInterface
{
    private ?ServerRequestInterface $request = null;

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->request = $request;
        return new ResponseStub();
    }

    public function setRequest(ServerRequestInterface $request): void
    {
        $this->request = $request;
    }

    public function lastRequest(): ?ServerRequestInterface
    {
        return $this->request;
    }
}
