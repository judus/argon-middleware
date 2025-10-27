<?php

declare(strict_types=1);

namespace Tests\Unit;

use Maduser\Argon\Middleware\Exception\MiddlewareResolverException;
use Maduser\Argon\Middleware\Resolver\StaticMiddlewareResolver;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class StaticMiddlewareResolverTest extends TestCase
{
    public function testConstructorRejectsNonMiddlewareInstances(): void
    {
        $this->expectException(MiddlewareResolverException::class);
        $this->expectExceptionMessage("Resolved instance for 'stdClass' must implement MiddlewareInterface. Got stdClass.");

        new StaticMiddlewareResolver([
            \stdClass::class => new \stdClass(),
        ]);
    }

    public function testResolveThrowsWhenMiddlewareNotRegistered(): void
    {
        $resolver = new StaticMiddlewareResolver([]);

        $this->expectException(MiddlewareResolverException::class);
        $this->expectExceptionMessage("No middleware registered for class 'Tests\\Unit\\StubMiddleware'.");

        $resolver->resolve(StubMiddleware::class);
    }
}

final class StubMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return $handler->handle($request);
    }
}
