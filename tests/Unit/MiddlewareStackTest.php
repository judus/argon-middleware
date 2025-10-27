<?php

declare(strict_types=1);

namespace Tests\Unit;

use Maduser\Argon\Middleware\MiddlewareStack;
use PHPUnit\Framework\TestCase;

final class MiddlewareStackTest extends TestCase
{
    public function testGetIdIsDeterministicForSameSequence(): void
    {
        $first = new MiddlewareStack(['Foo', 'Bar']);
        $second = new MiddlewareStack(['Foo', 'Bar']);
        $differentOrder = new MiddlewareStack(['Bar', 'Foo']);

        self::assertSame($first->getId(), $second->getId());
        self::assertNotSame($first->getId(), $differentOrder->getId());
    }

    public function testToArrayReturnsOriginalMiddlewareList(): void
    {
        $middlewares = ['Foo', 'Bar'];
        $stack = new MiddlewareStack($middlewares);

        self::assertSame($middlewares, $stack->toArray());
    }
}
