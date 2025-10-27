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

final class MiddlewarePipelineTest extends TestCase
{
    public function testHandleUsesRequestProvidedViaSetter(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $expectedRequest = $this->createMock(ServerRequestInterface::class);
        $incomingRequest = $this->createMock(ServerRequestInterface::class);

        $finalHandler = new class($response, $this, $expectedRequest) implements RequestHandlerInterface {
            public function __construct(
                private ResponseInterface $response,
                private TestCase $testCase,
                private ServerRequestInterface $expectedRequest
            ) {
            }

            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                $this->testCase->assertSame($this->expectedRequest, $request);
                return $this->response;
            }
        };

        $middleware = new class($this, $expectedRequest) implements MiddlewareInterface {
            public function __construct(
                private TestCase $testCase,
                private ServerRequestInterface $expectedRequest
            ) {
            }

            public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
            {
                $this->testCase->assertSame($this->expectedRequest, $request);
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

        $pipeline->setRequest($expectedRequest);

        self::assertSame($response, $pipeline->handle($incomingRequest));
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
