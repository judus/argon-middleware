<?php

declare(strict_types=1);

namespace Tests\Unit;

use Maduser\Argon\Middleware\Contracts\MiddlewareResolverInterface;
use Maduser\Argon\Middleware\Exception\MiddlewareException;
use Maduser\Argon\Middleware\MiddlewarePipelineBuilder;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\NullLogger;

final class MiddlewarePipelineBuilderTest extends TestCase
{
    public function testBuildInvokesResolverAndCreatesPipeline(): void
    {
        $middleware = new class implements MiddlewareInterface {
            public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
            {
                return $handler->handle($request);
            }
        };

        $resolver = $this->createMock(MiddlewareResolverInterface::class);
        $resolver->expects(self::once())
            ->method('resolve')
            ->with(TestMiddleware::class)
            ->willReturn($middleware);

        $response = $this->createMock(ResponseInterface::class);

        $finalHandler = new class($response) implements RequestHandlerInterface {
            public function __construct(private ResponseInterface $response)
            {
            }

            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                return $this->response;
            }
        };

        $builder = new MiddlewarePipelineBuilder($resolver, new NullLogger());
        $builder->addMiddleware(TestMiddleware::class);

        $pipeline = $builder->build($finalHandler);

        $request = $this->createMock(ServerRequestInterface::class);

        self::assertSame($response, $pipeline->handle($request));
    }
}

final class TestMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        throw new MiddlewareException('This stub should never be called directly.');
    }
}
