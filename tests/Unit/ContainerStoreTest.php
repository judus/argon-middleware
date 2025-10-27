<?php

declare(strict_types=1);

namespace Tests\Unit;

use Maduser\Argon\Container\ArgonContainer;
use Maduser\Argon\Middleware\Exception\PipelineStoreException;
use Maduser\Argon\Middleware\Store\ContainerStore;
use PHPUnit\Framework\TestCase;
use Psr\Http\Server\RequestHandlerInterface;

final class ContainerStoreTest extends TestCase
{
    public function testGetRequestHandlerReturnsHandlerFromContainer(): void
    {
        $handler = $this->createMock(RequestHandlerInterface::class);

        $container = $this->createMock(ArgonContainer::class);
        $container->expects(self::once())
            ->method('get')
            ->with('pipeline.id')
            ->willReturn($handler);

        $store = new ContainerStore($container);

        self::assertSame($handler, $store->getRequestHandler('pipeline.id'));
    }

    public function testGetRequestHandlerThrowsWhenServiceIsNotHandler(): void
    {
        $container = $this->createMock(ArgonContainer::class);
        $container->expects(self::once())
            ->method('get')
            ->with('pipeline.id')
            ->willReturn(new \stdClass());

        $store = new ContainerStore($container);

        $this->expectException(PipelineStoreException::class);
        $this->expectExceptionMessage('Container service [pipeline.id] must be a RequestHandlerInterface. Got stdClass.');

        $store->getRequestHandler('pipeline.id');
    }
}
