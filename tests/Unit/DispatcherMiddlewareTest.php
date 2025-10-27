<?php

declare(strict_types=1);

namespace Tests\Unit;

use Maduser\Argon\Middleware\Contracts\Middleware\DispatcherInterface;
use Maduser\Argon\Middleware\Contracts\ResultContextInterface;
use Maduser\Argon\Middleware\Middleware\Dispatcher;
use Maduser\Argon\Middleware\Support\Html;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

final class DispatcherMiddlewareTest extends TestCase
{
    public function testProcessDispatchesPlaceholderHtmlAndCallsNext(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $messages = [];
        $logger->expects(self::exactly(2))
            ->method('info')
            ->willReturnCallback(function (string $message, array $context = []) use (&$messages): void {
                $messages[] = [$message, $context];
            });

        $middleware = new Dispatcher($logger);

        $request = (new Psr17Factory())->createServerRequest('GET', '/');
        $response = $this->createMock(ResponseInterface::class);
        $handler = $this->createMock(RequestHandlerInterface::class);
        $captured = null;
        $handler->expects(self::once())
            ->method('handle')
            ->willReturnCallback(function ($incoming) use (&$captured, $response) {
                $captured = $incoming;
                return $response;
            });

        self::assertSame($response, $middleware->process($request, $handler));

        self::assertInstanceOf(ResultContextInterface::class, $captured->getAttribute(ResultContextInterface::class));
        $result = $captured->getAttribute(ResultContextInterface::class)->get();
        self::assertInstanceOf(Html::class, $result);
        self::assertStringContainsString('Argon Prophecy', (string) $result);
        self::assertStringContainsString('\\' . DispatcherInterface::class, (string) $result);

        self::assertSame([
            ['DispatcherMiddleware executing dispatch()', []],
            ['Dispatching placeholder logic', []],
        ], $messages);
    }
}
