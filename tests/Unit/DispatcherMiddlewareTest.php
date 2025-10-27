<?php

declare(strict_types=1);

namespace Tests\Unit;

use Maduser\Argon\Middleware\Contracts\Middleware\DispatcherInterface;
use Maduser\Argon\Middleware\Middleware\Dispatcher;
use Maduser\Argon\Middleware\ResultContext;
use Maduser\Argon\Middleware\Support\Html;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

final class DispatcherMiddlewareTest extends TestCase
{
    public function testProcessDispatchesPlaceholderHtmlAndCallsNext(): void
    {
        $context = new ResultContext();

        $logger = $this->createMock(LoggerInterface::class);
        $messages = [];
        $logger->expects(self::exactly(2))
            ->method('info')
            ->willReturnCallback(function (string $message, array $context = []) use (&$messages): void {
                $messages[] = [$message, $context];
            });

        $middleware = new Dispatcher($context, $logger);

        $request = $this->createMock(ServerRequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects(self::once())
            ->method('handle')
            ->with($request)
            ->willReturn($response);

        self::assertSame($response, $middleware->process($request, $handler));

        $result = $context->get();
        self::assertInstanceOf(Html::class, $result);
        self::assertStringContainsString('Argon Prophecy', (string) $result);
        self::assertStringContainsString('\\' . DispatcherInterface::class, (string) $result);

        self::assertSame([
            ['DispatcherMiddleware executing dispatch()', []],
            ['Dispatching placeholder logic', []],
        ], $messages);
    }
}
