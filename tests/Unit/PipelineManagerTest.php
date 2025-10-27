<?php

declare(strict_types=1);

namespace Tests\Unit;

use Maduser\Argon\Middleware\Contracts\MiddlewareStackInterface;
use Maduser\Argon\Middleware\Contracts\PipelineStoreInterface;
use Maduser\Argon\Middleware\PipelineManager;
use PHPUnit\Framework\TestCase;
use Psr\Http\Server\RequestHandlerInterface;

final class PipelineManagerTest extends TestCase
{
    public function testRegisterDelegatesToStore(): void
    {
        $stack = $this->createMock(MiddlewareStackInterface::class);

        $store = $this->createMock(PipelineStoreInterface::class);
        $store->expects(self::once())
            ->method('register')
            ->with($stack);

        $manager = new PipelineManager($store);
        $manager->register($stack);
    }

    public function testGetDelegatesToStore(): void
    {
        $store = $this->createMock(PipelineStoreInterface::class);
        $handler = $this->createMock(RequestHandlerInterface::class);

        $store->expects(self::once())
            ->method('get')
            ->with('pipeline.id')
            ->willReturn($handler);

        $manager = new PipelineManager($store);

        self::assertSame($handler, $manager->get('pipeline.id'));
    }
}
