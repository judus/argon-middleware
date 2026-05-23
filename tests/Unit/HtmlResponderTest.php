<?php

declare(strict_types=1);

namespace Tests\Unit;

use Maduser\Argon\Middleware\Contracts\ResultContextInterface;
use Maduser\Argon\Middleware\Middleware\HtmlResponder;
use Maduser\Argon\Middleware\ResultContext;
use Maduser\Argon\Support\Helper\Html;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Tests\Unit\Fixtures\MinimalResponseFactory;

final class HtmlResponderTest extends TestCase
{
    public function testProcessReturnsHtmlResponseWhenResultImplementsInterface(): void
    {
        $factory = new MinimalResponseFactory();
        $html = Html::create('<strong>{{ name }}</strong>', ['name' => 'Argon']);
        $context = (new ResultContext())->set($html);

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())
            ->method('info')
            ->with('Creating response', [
                'class' => HtmlResponder::class,
                'type' => 'text/html; charset=UTF-8'
            ]);

        $responder = new HtmlResponder($factory, $factory, $logger);

        $request = (new Psr17Factory())->createServerRequest('GET', '/')
            ->withAttribute(ResultContextInterface::class, $context);
        $next = $this->createMock(RequestHandlerInterface::class);
        $next->expects(self::never())->method('handle');

        $response = $responder->process($request, $next);

        self::assertSame('text/html; charset=UTF-8', $response->getHeaderLine('content-type'));
        self::assertSame('<strong>Argon</strong>', (string) $response->getBody());
    }

    public function testProcessDelegatesWhenResultIsNotHtmlable(): void
    {
        $factory = new MinimalResponseFactory();
        $context = new ResultContext();

        $responder = new HtmlResponder($factory, $factory, null);

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
