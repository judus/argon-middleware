<?php

declare(strict_types=1);

namespace Tests\Unit;

use Maduser\Argon\Middleware\Contracts\MiddlewarePipelineCacheInterface;
use Maduser\Argon\Middleware\Contracts\MiddlewareResolverInterface;
use Maduser\Argon\Middleware\MiddlewarePipeline;
use Maduser\Argon\Middleware\MiddlewarePipelineCache;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

final class MiddlewarePipelineCacheTest extends TestCase
{
    public function testCacheStoresPipelineByKey(): void
    {
        $cache = new MiddlewarePipelineCache();

        self::assertInstanceOf(MiddlewarePipelineCacheInterface::class, $cache);
        self::assertNull($cache->get('missing'));

        $pipeline = new MiddlewarePipeline([], $this->createMock(MiddlewareResolverInterface::class), new NullLogger());
        $cache->set('pipeline', $pipeline);

        self::assertSame($pipeline, $cache->get('pipeline'));
    }

    public function testCacheOverwritesPipelineForExistingKey(): void
    {
        $cache = new MiddlewarePipelineCache();
        $resolver = $this->createMock(MiddlewareResolverInterface::class);

        $original = new MiddlewarePipeline([], $resolver, new NullLogger());
        $replacement = new MiddlewarePipeline([], $resolver, new NullLogger());

        $cache->set('pipeline', $original);
        $cache->set('pipeline', $replacement);

        self::assertSame($replacement, $cache->get('pipeline'));
    }
}
