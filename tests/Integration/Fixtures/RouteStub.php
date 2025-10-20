<?php

declare(strict_types=1);

namespace Tests\Integration\Fixtures;

use Maduser\Argon\Routing\Contracts\RouteInterface;
use Psr\Http\Server\MiddlewareInterface;

final class RouteStub implements RouteInterface
{
    /** @var list<class-string<MiddlewareInterface>|MiddlewareInterface> */
    private array $middlewares = [];

    public function __construct(
        private readonly string $name = 'test',
        private readonly string $pattern = '/',
        private readonly string $method = 'GET',
        private readonly string|array $handler = 'handler',
        private ?string $pipelineId = null,
        private array $arguments = []
    ) {
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getPattern(): string
    {
        return $this->pattern;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getHandler(): string|array
    {
        return $this->handler;
    }

    public function setPipelineId(?string $pipelineId): void
    {
        $this->pipelineId = $pipelineId;
    }

    public function getPipelineId(): ?string
    {
        return $this->pipelineId;
    }

    public function setMiddlewares(array $middlewares): void
    {
        $this->middlewares = $middlewares;
    }

    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }

    public function setArguments(array $args): void
    {
        $this->arguments = $args;
    }

    public function getArguments(): array
    {
        return $this->arguments;
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'pattern' => $this->pattern,
            'method' => $this->method,
            'handler' => $this->handler,
            'pipeline' => $this->pipelineId,
            'middlewares' => $this->middlewares,
            'arguments' => $this->arguments,
        ];
    }
}
