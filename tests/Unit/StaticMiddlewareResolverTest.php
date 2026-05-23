<?php

declare(strict_types=1);

namespace Tests\Unit;

use Maduser\Argon\Middleware\Exception\MiddlewareResolverException;
use Maduser\Argon\Middleware\Resolver\StaticMiddlewareResolver;
use PHPUnit\Framework\TestCase;
use Tests\Unit\Fixtures\StubMiddleware;

final class StaticMiddlewareResolverTest extends TestCase
{
    public function testConstructorRejectsNonMiddlewareInstances(): void
    {
        $this->expectException(MiddlewareResolverException::class);
        $this->expectExceptionMessage(
            "Resolved instance for 'stdClass' must implement MiddlewareInterface. Got stdClass."
        );

        /** @psalm-suppress InvalidArgument Testing runtime validation for invalid resolver entries. */
        new StaticMiddlewareResolver([
            \stdClass::class => new \stdClass(),
        ]);
    }

    public function testResolveThrowsWhenMiddlewareNotRegistered(): void
    {
        $resolver = new StaticMiddlewareResolver([]);

        $this->expectException(MiddlewareResolverException::class);
        $this->expectExceptionMessage(
            "No middleware registered for class 'Tests\\Unit\\Fixtures\\StubMiddleware'."
        );

        $resolver->resolve(StubMiddleware::class);
    }

    public function testResolveReturnsRegisteredMiddleware(): void
    {
        $middleware = new StubMiddleware();
        $resolver = new StaticMiddlewareResolver([
            StubMiddleware::class => $middleware,
        ]);

        self::assertSame($middleware, $resolver->resolve(StubMiddleware::class));
    }
}
