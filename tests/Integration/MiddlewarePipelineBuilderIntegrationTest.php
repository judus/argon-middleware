<?php

declare(strict_types=1);

namespace Tests\Integration;

use Maduser\Argon\Middleware\MiddlewarePipelineBuilder;
use Maduser\Argon\Middleware\MiddlewareVerbosity;
use Maduser\Argon\Middleware\Resolver\StaticMiddlewareResolver;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Tests\Integration\Fixtures\CollectingLogger;
use Tests\Integration\Fixtures\LogCollector;
use Tests\Integration\Fixtures\RecordingMiddleware;
use Tests\Unit\Fixtures\ResponseStub;

final class MiddlewarePipelineBuilderIntegrationTest extends TestCase
{
    public function testPipelineUsesPresetRequestAndFinalHandlerIsLogged(): void
    {
        $collector = new LogCollector();

        $resolver = new StaticMiddlewareResolver([
            RecordingMiddleware::class => new RecordingMiddleware($collector, 'builder-recording'),
        ]);

        $logger = new CollectingLogger();
        $builder = new MiddlewarePipelineBuilder($resolver, $logger);
        $builder->setVerbosity(MiddlewareVerbosity::DEBUG);
        $builder->addMiddleware(RecordingMiddleware::class);

        $finalHandler = new class implements RequestHandlerInterface
        {
            public ?ServerRequestInterface $lastRequest = null;

            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                $this->lastRequest = $request;
                return new ResponseStub();
            }
        };

        $pipeline = $builder->build($finalHandler);

        $presetRequest = $this->createMock(ServerRequestInterface::class);
        $incomingRequest = $this->createMock(ServerRequestInterface::class);

        $pipeline->setRequest($presetRequest);
        $result = $pipeline->handle($incomingRequest);

        self::assertInstanceOf(ResponseStub::class, $result);
        self::assertSame(['builder-recording'], $collector->entries());
        self::assertSame($presetRequest, $finalHandler->lastRequest);

        $logMessages = array_map(static fn(array $record) => $record['message'], $logger->records());
        self::assertContains('Final handler invoked', $logMessages);
    }
}
