<?php

declare(strict_types=1);

namespace Tests\Unit;

use Maduser\Argon\Middleware\MiddlewareStack;
use PHPUnit\Framework\TestCase;
use Tests\Unit\Fixtures\StubMiddleware;
use Tests\Unit\Fixtures\TestMiddleware;

final class MiddlewareStackTest extends TestCase
{
    public function testGetIdIsDeterministicForSameSequence(): void
    {
        $first = new MiddlewareStack([StubMiddleware::class, TestMiddleware::class]);
        $second = new MiddlewareStack([StubMiddleware::class, TestMiddleware::class]);
        $differentOrder = new MiddlewareStack([TestMiddleware::class, StubMiddleware::class]);

        self::assertSame($first->getId(), $second->getId());
        self::assertNotSame($first->getId(), $differentOrder->getId());
    }

    public function testToArrayReturnsOriginalMiddlewareList(): void
    {
        $middlewares = [StubMiddleware::class, TestMiddleware::class];
        $stack = new MiddlewareStack($middlewares);

        self::assertSame($middlewares, $stack->toArray());
    }
}
