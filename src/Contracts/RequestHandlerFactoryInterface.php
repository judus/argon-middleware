<?php

declare(strict_types=1);

namespace Maduser\Argon\Middleware\Contracts;

use Maduser\Argon\Middleware\MiddlewarePipeline;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

interface RequestHandlerFactoryInterface
{
    public function create(string $cacheKey = 'http_pipeline'): RequestHandlerInterface;

    /** @param list<class-string|MiddlewareInterface> $middleware */
    public function createFromStack(array $middleware): MiddlewarePipeline;
}
