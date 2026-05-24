<?php

declare(strict_types=1);

namespace Tests\Unit;

use Maduser\Argon\Middleware\Contracts\MiddlewareResolverInterface;
use Maduser\Argon\Middleware\MiddlewarePipeline;
use Maduser\Argon\Middleware\MiddlewareVerbosity;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\NullLogger;
use Tests\Unit\Fixtures\StubMiddleware;

final class MiddlewarePipelineTest extends TestCase
{
    public function testHandleInvokesFinalHandler(): void
    {
        $response = $this->createMock(ResponseInterface::class);

        $finalHandler = new class ($response) implements RequestHandlerInterface {
            public ?ServerRequestInterface $handled = null;

            public function __construct(private ResponseInterface $response)
            {
            }

            #[\Override]
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                $this->handled = $request;
                return $this->response;
            }
        };

        $middleware = new class implements MiddlewareInterface {
            #[\Override]
            public function process(
                ServerRequestInterface $request,
                RequestHandlerInterface $handler
            ): ResponseInterface {
                return $handler->handle($request);
            }
        };

        $pipeline = new MiddlewarePipeline(
            middleware: [$middleware],
            resolver: $this->createMock(MiddlewareResolverInterface::class),
            logger: new NullLogger(),
            finalHandler: $finalHandler,
            verbosity: MiddlewareVerbosity::NORMAL
        );

        $request = (new \Nyholm\Psr7\Factory\Psr17Factory())->createServerRequest('GET', '/pipeline');

        self::assertSame($response, $pipeline->handle($request));
        self::assertInstanceOf(ServerRequestInterface::class, $finalHandler->handled);
        self::assertSame($request, $finalHandler->handled);
    }

    public function testHandleResolvesClassStringsViaResolver(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $request = $this->createMock(ServerRequestInterface::class);

        $finalHandler = new class ($response) implements RequestHandlerInterface {
            public function __construct(private ResponseInterface $response)
            {
            }

            #[\Override]
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                return $this->response;
            }
        };

        $middleware = new class ($this) implements MiddlewareInterface {
            public function __construct(private TestCase $testCase)
            {
            }

            #[\Override]
            public function process(
                ServerRequestInterface $request,
                RequestHandlerInterface $handler
            ): ResponseInterface {
                $this->testCase->assertInstanceOf(ServerRequestInterface::class, $request);
                return $handler->handle($request);
            }
        };

        $resolver = $this->createMock(MiddlewareResolverInterface::class);
        $resolver->expects(self::once())
            ->method('resolve')
            ->with(StubMiddleware::class)
            ->willReturn($middleware);

        $pipeline = new MiddlewarePipeline(
            middleware: [StubMiddleware::class],
            resolver: $resolver,
            logger: new NullLogger(),
            finalHandler: $finalHandler,
            verbosity: MiddlewareVerbosity::NORMAL
        );

        self::assertSame($response, $pipeline->handle($request));
    }
}
