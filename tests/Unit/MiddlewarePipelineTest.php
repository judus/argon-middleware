<?php

declare(strict_types=1);

namespace Tests\Unit;

use Maduser\Argon\Middleware\Contracts\MiddlewareResolverInterface;
use Maduser\Argon\Middleware\Contracts\ResultContextInterface;
use Maduser\Argon\Middleware\MiddlewarePipeline;
use Maduser\Argon\Middleware\MiddlewareVerbosity;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\NullLogger;

final class MiddlewarePipelineTest extends TestCase
{
    public function testHandleSeedsResultContextAndInvokesFinalHandler(): void
    {
        $response = $this->createMock(ResponseInterface::class);

        $finalHandler = new class($response) implements RequestHandlerInterface {
            public ?ServerRequestInterface $handled = null;

            public function __construct(private ResponseInterface $response)
            {
            }

            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                $this->handled = $request;
                return $this->response;
            }
        };

        $middleware = new class implements MiddlewareInterface {
            public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
            {
                TestCase::assertInstanceOf(ResultContextInterface::class, $request->getAttribute(ResultContextInterface::class));
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
        self::assertInstanceOf(ResultContextInterface::class, $finalHandler->handled->getAttribute(ResultContextInterface::class));
    }

    public function testHandleResolvesClassStringsViaResolver(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $request = $this->createMock(ServerRequestInterface::class);

        $finalHandler = new class($response) implements RequestHandlerInterface {
            public function __construct(private ResponseInterface $response)
            {
            }

            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                return $this->response;
            }
        };

        $middleware = new class($this) implements MiddlewareInterface {
            public function __construct(private TestCase $testCase)
            {
            }

            public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
            {
                $this->testCase->assertInstanceOf(ServerRequestInterface::class, $request);
                return $handler->handle($request);
            }
        };

        $resolver = $this->createMock(MiddlewareResolverInterface::class);
        $resolver->expects(self::once())
            ->method('resolve')
            ->with('middleware.stub')
            ->willReturn($middleware);

        $pipeline = new MiddlewarePipeline(
            middleware: ['middleware.stub'],
            resolver: $resolver,
            logger: new NullLogger(),
            finalHandler: $finalHandler,
            verbosity: MiddlewareVerbosity::NORMAL
        );

        self::assertSame($response, $pipeline->handle($request));
    }
}
