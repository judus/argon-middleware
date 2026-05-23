<?php

declare(strict_types=1);

namespace Tests\Integration;

use Maduser\Argon\Middleware\Contracts\ResultContextInterface;
use Maduser\Argon\Middleware\MiddlewarePipelineBuilder;
use Maduser\Argon\Middleware\MiddlewareVerbosity;
use Maduser\Argon\Middleware\Resolver\StaticMiddlewareResolver;
use Nyholm\Psr7\Factory\Psr17Factory;
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

            #[\Override]
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                $this->lastRequest = $request;
                return new ResponseStub();
            }
        };

        $pipeline = $builder->build($finalHandler);

        $incomingRequest = (new Psr17Factory())->createServerRequest('GET', '/pipeline-test');

        $result = $pipeline->handle($incomingRequest);

        self::assertInstanceOf(ResponseStub::class, $result);
        self::assertSame(['builder-recording'], $collector->entries());
        self::assertNotNull($finalHandler->lastRequest);
        self::assertInstanceOf(
            ResultContextInterface::class,
            $finalHandler->lastRequest->getAttribute(ResultContextInterface::class)
        );

        $logMessages = array_map(static fn(array $record) => $record['message'], $logger->records());
        self::assertContains('Final handler invoked', $logMessages);
    }
}
