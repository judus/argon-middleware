<?php

declare(strict_types=1);

namespace Tests\Unit;

use JsonSerializable;
use Maduser\Argon\Middleware\Contracts\ResultContextInterface;
use Maduser\Argon\Middleware\Middleware\JsonResponder;
use Maduser\Argon\Middleware\ResultContext;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Tests\Unit\Fixtures\MinimalResponseFactory;

final class JsonResponderTest extends TestCase
{
    public function testProcessSerialisesArrayResult(): void
    {
        $factory = new MinimalResponseFactory();
        $context = (new ResultContext())->set(['status' => 'ok']);

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())
            ->method('info')
            ->with('Creating response', [
                'class' => JsonResponder::class,
                'type' => 'application/json; charset=UTF-8'
            ]);

        $responder = new JsonResponder($factory, $factory, $logger);

        $request = (new Psr17Factory())->createServerRequest('GET', '/')
            ->withAttribute(ResultContextInterface::class, $context);
        $finalHandler = $this->createMock(RequestHandlerInterface::class);
        $finalHandler->expects(self::never())->method('handle');

        $response = $responder->process($request, $finalHandler);

        self::assertSame('application/json; charset=UTF-8', $response->getHeaderLine('content-type'));
        self::assertSame('{"status":"ok"}', (string) $response->getBody());
    }

    public function testProcessSerialisesJsonSerializableResult(): void
    {
        $factory = new MinimalResponseFactory();
        $payload = new class implements JsonSerializable {
            #[\Override]
            public function jsonSerialize(): array
            {
                return ['value' => 42];
            }
        };

        $context = (new ResultContext())->set($payload);

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())
            ->method('info')
            ->with('Creating response', [
                'class' => JsonResponder::class,
                'type' => 'application/json; charset=UTF-8'
            ]);

        $responder = new JsonResponder($factory, $factory, $logger);

        $request = (new Psr17Factory())->createServerRequest('GET', '/')
            ->withAttribute(ResultContextInterface::class, $context);
        $finalHandler = $this->createMock(RequestHandlerInterface::class);

        $result = $responder->process($request, $finalHandler);

        self::assertSame('{"value":42}', (string) $result->getBody());
    }

    public function testProcessDelegatesWhenResultIsNotJsonCompatible(): void
    {
        $factory = new MinimalResponseFactory();
        $context = (new ResultContext())->set('plain');

        $responder = new JsonResponder($factory, $factory, null);

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
