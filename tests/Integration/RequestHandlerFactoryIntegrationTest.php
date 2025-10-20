<?php

declare(strict_types=1);

namespace Tests\Integration;

use InvalidArgumentException;
use Maduser\Argon\Middleware\Factory\RequestHandlerFactory;
use Maduser\Argon\Middleware\Loader\StaticMiddlewareLoader;
use Maduser\Argon\Middleware\MiddlewareDefinition;
use Maduser\Argon\Middleware\Resolver\StaticMiddlewareResolver;
use Maduser\Argon\Routing\Contracts\RequestHandlerResolverInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Tests\Integration\Fixtures\CollectingLogger;
use Tests\Integration\Fixtures\LogCollector;
use Tests\Integration\Fixtures\RecordingMiddleware;
use Tests\Integration\Fixtures\RouteContextStub;
use Tests\Integration\Fixtures\RouteStub;
use Tests\Integration\Fixtures\SettableRequestHandler;
use Tests\Integration\Fixtures\TerminalMiddleware;
use Tests\Unit\Fixtures\ResponseStub;

final class RequestHandlerFactoryIntegrationTest extends TestCase
{
    public function testCreateBuildsPipelineFromLoaderAndExecutesMiddlewareChain(): void
    {
        $collector = new LogCollector();
        $response = new ResponseStub();

        $resolver = new StaticMiddlewareResolver([
            RecordingMiddleware::class => new RecordingMiddleware($collector, 'recording'),
            TerminalMiddleware::class => new TerminalMiddleware($collector, 'terminal', $response),
        ]);

        $loader = new StaticMiddlewareLoader([
            MiddlewareDefinition::DEFAULT_GROUP => [
                new MiddlewareDefinition(RecordingMiddleware::class, 10),
            ],
            'custom' => [
                new MiddlewareDefinition(TerminalMiddleware::class, 0),
            ],
        ]);

        $logger = new CollectingLogger();

        $handler = new SettableRequestHandler();
        $requestResolver = new class($handler) implements RequestHandlerResolverInterface
        {
            public function __construct(private readonly SettableRequestHandler $handler)
            {
            }

            public function resolve(?ServerRequestInterface $request = null): RequestHandlerInterface
            {
                return $this->handler;
            }
        };

        $factory = new RequestHandlerFactory(
            resolver: $resolver,
            requestHandlerResolver: $requestResolver,
            context: new RouteContextStub(new RouteStub()),
            logger: $logger,
            loader: $loader
        );

        $pipeline = $factory->create();
        $request = $this->createMock(ServerRequestInterface::class);
        $result = $pipeline->handle($request);

        self::assertInstanceOf(ResponseStub::class, $result);
        self::assertSame(['recording', 'terminal'], $collector->entries());
        self::assertCount(2, $logger->records(), 'Expected middleware execution logs');
    }

    public function testCreateUsesCachedPipelineWhenAvailable(): void
    {
        $handler = $this->createMock(RequestHandlerInterface::class);

        $resolver = new StaticMiddlewareResolver([]);
        $requestResolver = new class($handler) implements RequestHandlerResolverInterface
        {
            public function __construct(private readonly RequestHandlerInterface $handler)
            {
            }

            public function resolve(?ServerRequestInterface $request = null): RequestHandlerInterface
            {
                return $this->handler;
            }
        };

        $pipelineManager = new class($handler) implements \Maduser\Argon\Middleware\Contracts\PipelineManagerInterface
        {
            public function __construct(private readonly RequestHandlerInterface $handler)
            {
            }

            public function register(\Maduser\Argon\Middleware\Contracts\MiddlewareStackInterface $stack): void
            {
                // No-op for the cache stub.
            }

            public function get(\Maduser\Argon\Middleware\Contracts\MiddlewareStackInterface|string $keyOrStack): RequestHandlerInterface
            {
                return $this->handler;
            }
        };

        $factory = new RequestHandlerFactory(
            resolver: $resolver,
            requestHandlerResolver: $requestResolver,
            context: new RouteContextStub(new RouteStub()),
            logger: new CollectingLogger(),
            loader: new StaticMiddlewareLoader([]),
            pipelines: $pipelineManager
        );

        $result = $factory->create('cached_key');

        self::assertSame($handler, $result);
    }

    public function testCreateFromStackReturnsConfiguredPipeline(): void
    {
        $collector = new LogCollector();
        $response = new ResponseStub();

        $resolver = new StaticMiddlewareResolver([
            RecordingMiddleware::class => new RecordingMiddleware($collector, 'stack-recording'),
            TerminalMiddleware::class => new TerminalMiddleware($collector, 'stack-terminal', $response),
        ]);

        $factory = new RequestHandlerFactory(
            resolver: $resolver,
            requestHandlerResolver: new class implements RequestHandlerResolverInterface
            {
                public function resolve(?ServerRequestInterface $request = null): RequestHandlerInterface
                {
                    throw new InvalidArgumentException('Not used in this scenario.');
                }
            },
            context: new RouteContextStub(new RouteStub()),
            logger: new CollectingLogger(),
            loader: new StaticMiddlewareLoader([])
        );

        $pipeline = $factory->createFromStack([
            RecordingMiddleware::class,
            new TerminalMiddleware($collector, 'stack-terminal', $response),
        ]);

        $request = $this->createMock(ServerRequestInterface::class);
        $result = $pipeline->handle($request);

        self::assertInstanceOf(ResponseStub::class, $result);
        self::assertSame(
            ['stack-recording', 'stack-terminal'],
            $collector->entries()
        );
    }

    public function testCreateFromRouteContextResolvesRequestHandlerAndInjectsRequest(): void
    {
        $handler = new SettableRequestHandler();

        $resolver = new StaticMiddlewareResolver([]);
        $requestResolver = new class($handler) implements RequestHandlerResolverInterface
        {
            public function __construct(private readonly SettableRequestHandler $handler)
            {
            }

            public function resolve(?ServerRequestInterface $request = null): RequestHandlerInterface
            {
                return $this->handler;
            }
        };

        $factory = new RequestHandlerFactory(
            resolver: $resolver,
            requestHandlerResolver: $requestResolver,
            context: new RouteContextStub(new RouteStub()),
            logger: new CollectingLogger(),
            loader: new StaticMiddlewareLoader([])
        );

        $request = $this->createMock(ServerRequestInterface::class);

        $wrapper = $factory->createFromRouteContext($request);
        $response = $wrapper->handle($request);

        self::assertInstanceOf(ResponseStub::class, $response);
        self::assertSame($request, $handler->lastRequest());
    }
}
