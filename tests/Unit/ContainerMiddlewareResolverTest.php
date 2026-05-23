<?php

declare(strict_types=1);

namespace Tests\Unit;

use Maduser\Argon\Middleware\Exception\MiddlewareResolverException;
use Maduser\Argon\Middleware\Resolver\ContainerMiddlewareResolver;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class ContainerMiddlewareResolverTest extends TestCase
{
    public function testResolveReturnsMiddlewareFromContainer(): void
    {
        $middleware = new class implements MiddlewareInterface {
            #[\Override]
            public function process(
                ServerRequestInterface $request,
                RequestHandlerInterface $handler
            ): ResponseInterface {
                return $handler->handle($request);
            }
        };

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::once())
            ->method('get')
            ->with('ExampleMiddleware')
            ->willReturn($middleware);

        $resolver = new ContainerMiddlewareResolver($container);

        self::assertSame($middleware, $resolver->resolve('ExampleMiddleware'));
    }

    public function testResolveThrowsWhenResolvedInstanceIsNotMiddleware(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::once())
            ->method('get')
            ->with('ExampleMiddleware')
            ->willReturn(new \stdClass());

        $resolver = new ContainerMiddlewareResolver($container);

        $this->expectException(MiddlewareResolverException::class);
        $this->expectExceptionMessage(
            "Resolved instance for 'ExampleMiddleware' must implement MiddlewareInterface. Got stdClass."
        );

        $resolver->resolve('ExampleMiddleware');
    }
}
