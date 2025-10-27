<?php

declare(strict_types=1);

namespace Tests\Unit;

use Maduser\Argon\Middleware\Contracts\MiddlewareResolverInterface;
use Maduser\Argon\Middleware\Exception\EmptyMiddlewareChainException;
use Maduser\Argon\Middleware\Exception\MiddlewareDispatcherException;
use Maduser\Argon\Middleware\MiddlewareDispatcher;
use Maduser\Argon\Middleware\MiddlewareVerbosity;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

final class MiddlewareDispatcherTest extends TestCase
{
    public function testHandleInvokesFinalHandlerWhenChainExhausted(): void
    {
        $resolver = $this->createMock(MiddlewareResolverInterface::class);

        $response = $this->createMock(ResponseInterface::class);
        $finalHandler = $this->createMock(RequestHandlerInterface::class);
        $finalHandler->expects(self::once())
            ->method('handle')
            ->willReturn($response);

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())
            ->method('info')
            ->with('Final handler invoked');

        $dispatcher = new MiddlewareDispatcher(
            middleware: [],
            resolver: $resolver,
            finalHandler: $finalHandler,
            logger: $logger,
            verbosity: MiddlewareVerbosity::DEBUG
        );

        $request = $this->createMock(ServerRequestInterface::class);

        self::assertSame($response, $dispatcher->handle($request));
    }

    public function testHandleResolvesMiddlewareFromClassString(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $request = $this->createMock(ServerRequestInterface::class);

        $finalHandler = new class($response, $this, $request) implements RequestHandlerInterface {
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

        $middleware = new class($this, $request) implements MiddlewareInterface {
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

        $resolver = $this->createMock(MiddlewareResolverInterface::class);
        $resolver->expects(self::once())
            ->method('resolve')
            ->with('stub')
            ->willReturn($middleware);

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())
            ->method('info')
            ->with('Executing middleware', ['middleware' => $middleware]);

        $dispatcher = new MiddlewareDispatcher(
            middleware: ['stub'],
            resolver: $resolver,
            finalHandler: $finalHandler,
            logger: $logger,
            verbosity: MiddlewareVerbosity::NORMAL
        );

        self::assertSame($response, $dispatcher->handle($request));
    }

    public function testHandleThrowsForInvalidMiddlewareEntry(): void
    {
        $resolver = $this->createMock(MiddlewareResolverInterface::class);
        $dispatcher = new MiddlewareDispatcher(
            middleware: [123],
            resolver: $resolver,
            finalHandler: null,
            logger: new NullLogger(),
            verbosity: MiddlewareVerbosity::NORMAL
        );

        $this->expectException(MiddlewareDispatcherException::class);
        $this->expectExceptionMessage('Invalid middleware entry at index 0. Expected class-string or MiddlewareInterface, got int.');

        $dispatcher->handle($this->createMock(ServerRequestInterface::class));
    }

    public function testHandleThrowsWhenChainExhaustedWithoutFinalHandler(): void
    {
        $resolver = $this->createMock(MiddlewareResolverInterface::class);

        $dispatcher = new MiddlewareDispatcher(
            middleware: [],
            resolver: $resolver,
            finalHandler: null,
            logger: new NullLogger(),
            verbosity: MiddlewareVerbosity::NONE
        );

        $this->expectException(EmptyMiddlewareChainException::class);

        $dispatcher->handle($this->createMock(ServerRequestInterface::class));
    }
}
