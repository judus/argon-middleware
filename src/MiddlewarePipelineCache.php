<?php

declare(strict_types=1);

namespace Maduser\Argon\Middleware;

use Maduser\Argon\Middleware\Contracts\MiddlewarePipelineCacheInterface;

final class MiddlewarePipelineCache implements MiddlewarePipelineCacheInterface
{
    /** @var array<string, MiddlewarePipeline> */
    private array $pipelines = [];

    #[\Override]
    public function get(string $key): ?MiddlewarePipeline
    {
        return $this->pipelines[$key] ?? null;
    }

    #[\Override]
    public function set(string $key, MiddlewarePipeline $pipeline): void
    {
        $this->pipelines[$key] = $pipeline;
    }
}
