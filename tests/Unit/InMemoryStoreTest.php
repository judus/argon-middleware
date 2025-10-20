<?php

declare(strict_types=1);

namespace Tests\Unit;

use Maduser\Argon\Middleware\MiddlewareStack;
use Maduser\Argon\Middleware\Store\InMemoryStore;
use PHPUnit\Framework\TestCase;
use Psr\Http\Server\RequestHandlerInterface;

final class InMemoryStoreTest extends TestCase
{
    public function testGetByIdentifierReturnsRequestHandler(): void
    {
        $stack = new MiddlewareStack([]);
        $store = new InMemoryStore();

        $store->register($stack);

        $handler = $store->get($stack->getId());

        self::assertInstanceOf(RequestHandlerInterface::class, $handler);
    }
}
