<?php

declare(strict_types=1);

namespace Tests\Unit;

use Maduser\Argon\Middleware\MiddlewareStack;
use Maduser\Argon\Middleware\Store\InMemoryStore;
use Maduser\Argon\Middleware\Exception\PipelineStoreException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Server\RequestHandlerInterface;

final class InMemoryStoreTest extends TestCase
{
    public function testGetByIdentifierReturnsRequestHandler(): void
    {
        $stack = new MiddlewareStack([]);
        $store = new InMemoryStore();

        self::assertSame($store, $store->register($stack));

        $handler = $store->get($stack->getId());

        self::assertInstanceOf(RequestHandlerInterface::class, $handler);
    }

    public function testGetThrowsWhenStackNotRegistered(): void
    {
        $store = new InMemoryStore();

        $this->expectException(PipelineStoreException::class);
        $this->expectExceptionMessage('Pipeline stack [missing] is not registered in the in-memory store.');

        $store->get('missing');
    }
}
