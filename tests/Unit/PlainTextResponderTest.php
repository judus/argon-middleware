<?php

declare(strict_types=1);

namespace Tests\Unit;

use Maduser\Argon\Middleware\Contracts\ResultContextInterface;
use Maduser\Argon\Middleware\Middleware\PlainTextResponder;
use Maduser\Argon\Middleware\ResultContext;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Tests\Unit\Fixtures\MinimalResponseFactory;

final class PlainTextResponderTest extends TestCase
{
    public function testProcessReturnsPlainTextResponseWhenResultIsString(): void
    {
        $factory = new MinimalResponseFactory();
        $context = (new ResultContext())->set('Hello World');

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())
            ->method('info')
            ->with('Creating response', [
                'class' => PlainTextResponder::class,
                'type' => 'text/plain; charset=UTF-8'
            ]);

        $responder = new PlainTextResponder($factory, $factory, $logger);

        $request = (new Psr17Factory())->createServerRequest('GET', '/')
            ->withAttribute(ResultContextInterface::class, $context);
        $finalHandler = $this->createMock(RequestHandlerInterface::class);
        $finalHandler->expects(self::never())->method('handle');

        $response = $responder->process($request, $finalHandler);

        self::assertSame('text/plain; charset=UTF-8', $response->getHeaderLine('content-type'));
        self::assertSame('Hello World', (string) $response->getBody());
    }

    public function testProcessDelegatesToNextWhenResultIsNotString(): void
    {
        $factory = new MinimalResponseFactory();
        $context = new ResultContext();

        $responder = new PlainTextResponder($factory, $factory, null);

        $request = (new Psr17Factory())->createServerRequest('GET', '/')
            ->withAttribute(ResultContextInterface::class, $context);
        $expectedResponse = $this->createMock(ResponseInterface::class);

        $finalHandler = $this->createMock(RequestHandlerInterface::class);
        $finalHandler->expects(self::once())
            ->method('handle')
            ->willReturn($expectedResponse);

        self::assertSame($expectedResponse, $responder->process($request, $finalHandler));
    }
}
