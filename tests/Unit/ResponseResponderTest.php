<?php

declare(strict_types=1);

namespace Tests\Unit;

use Maduser\Argon\Middleware\Contracts\ResultContextInterface;
use Maduser\Argon\Middleware\Middleware\ResponseResponder;
use Maduser\Argon\Middleware\ResultContext;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Tests\Unit\Fixtures\MinimalResponseFactory;

final class ResponseResponderTest extends TestCase
{
    public function testProcessReturnsResponseFromContext(): void
    {
        $factory = new MinimalResponseFactory();
        $response = $factory->createResponse()
            ->withBody($factory->createStream('payload'))
            ->withHeader('X-Test', '1');

        $context = (new ResultContext())->set($response);

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())
            ->method('info')
            ->with(ResponseResponder::class . ' forwards a response');

        $responder = new ResponseResponder($logger);

        $request = (new Psr17Factory())->createServerRequest('GET', '/')
            ->withAttribute(ResultContextInterface::class, $context);
        $next = $this->createMock(RequestHandlerInterface::class);
        $next->expects(self::never())->method('handle');

        $result = $responder->process($request, $next);

        self::assertSame($response, $result);
        self::assertSame('1', $result->getHeaderLine('x-test'));
        self::assertSame('payload', (string) $result->getBody());
    }

    public function testProcessDelegatesWhenContextDoesNotContainResponse(): void
    {
        $context = new ResultContext();
        $responder = new ResponseResponder(null);

        $request = (new Psr17Factory())->createServerRequest('GET', '/')
            ->withAttribute(ResultContextInterface::class, $context);
        $expected = $this->createMock(ResponseInterface::class);
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects(self::once())
            ->method('handle')
            ->willReturn($expected);

        self::assertSame($expected, $responder->process($request, $handler));
    }
}
