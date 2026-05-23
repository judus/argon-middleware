<?php

declare(strict_types=1);

namespace Maduser\Argon\Middleware;

use Maduser\Argon\Middleware\Contracts\MiddlewarePipelineCacheInterface;

final class MiddlewarePipelineCache implements MiddlewarePipelineCacheInterface
{
    #[\Override]
    public function get(string $key): ?MiddlewarePipeline
    {
        return null;
    }

    #[\Override]
    public function set(string $key, MiddlewarePipeline $pipeline): void
    {
        // TODO: Implement set() method.
    }
}
