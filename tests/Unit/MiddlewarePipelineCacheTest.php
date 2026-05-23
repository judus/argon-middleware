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
    public function testCacheImplementsContract(): void
    {
        $cache = new MiddlewarePipelineCache();

        self::assertInstanceOf(MiddlewarePipelineCacheInterface::class, $cache);
        self::assertNull($cache->get('missing'));

        $pipeline = new MiddlewarePipeline([], $this->createMock(MiddlewareResolverInterface::class), new NullLogger());
        $cache->set('pipeline', $pipeline);

        self::assertNull($cache->get('pipeline'));
    }
}
