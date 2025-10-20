<?php

declare(strict_types=1);

namespace Tests\Integration;

use Maduser\Argon\Container\ArgonContainer;
use Maduser\Argon\Middleware\MiddlewareStack;
use Maduser\Argon\Middleware\PipelineManager;
use Maduser\Argon\Middleware\Store\ContainerStore;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Tests\Integration\Fixtures\CollectingLogger;
use Tests\Integration\Fixtures\LogCollector;
use Tests\Integration\Fixtures\RecordingMiddleware;
use Tests\Integration\Fixtures\TerminalMiddleware;
use Tests\Unit\Fixtures\ResponseStub;
use Maduser\Argon\Middleware\Factory\RequestHandlerFactory;
use Maduser\Argon\Middleware\Loader\StaticMiddlewareLoader;
use Maduser\Argon\Middleware\MiddlewareDefinition;
use Maduser\Argon\Middleware\Resolver\StaticMiddlewareResolver;

final class ContainerStoreIntegrationTest extends TestCase
{
    public function testContainerStoreRegistersPipelineAndResolvesHandler(): void
    {
        $collector = new LogCollector();
        $response = new ResponseStub();

        $resolver = new StaticMiddlewareResolver([
            RecordingMiddleware::class => new RecordingMiddleware($collector, 'store-recording'),
            TerminalMiddleware::class => new TerminalMiddleware($collector, 'store-terminal', $response),
        ]);

        $factory = new RequestHandlerFactory(
            resolver: $resolver,
            logger: new CollectingLogger(),
            loader: new StaticMiddlewareLoader([
                MiddlewareDefinition::DEFAULT_GROUP => [
                    new MiddlewareDefinition(RecordingMiddleware::class, 1),
                    new MiddlewareDefinition(TerminalMiddleware::class, 0),
                ],
            ])
        );

        $container = new ArgonContainer();
        $container->set(RequestHandlerFactory::class, fn() => $factory);

        $store = new ContainerStore($container);

        $stack = new MiddlewareStack([
            RecordingMiddleware::class,
            TerminalMiddleware::class,
        ]);

        $handler = $store->get($stack);

        $request = $this->createMock(ServerRequestInterface::class);
        $result = $handler->handle($request);

        self::assertInstanceOf(ResponseStub::class, $result);
        self::assertSame(['store-recording', 'store-terminal'], $collector->entries());
    }

    public function testPipelineManagerDelegatesToStore(): void
    {
        $collector = new LogCollector();
        $response = new ResponseStub();

        $resolver = new StaticMiddlewareResolver([
            TerminalMiddleware::class => new TerminalMiddleware($collector, 'manager-terminal', $response),
        ]);

        $factory = new RequestHandlerFactory(
            resolver: $resolver,
            logger: new CollectingLogger(),
            loader: new StaticMiddlewareLoader([])
        );

        $container = new ArgonContainer();
        $container->set(RequestHandlerFactory::class, fn() => $factory);

        $store = new ContainerStore($container);
        $manager = new PipelineManager($store);

        $stack = new MiddlewareStack([
            TerminalMiddleware::class,
        ]);

        $manager->register($stack);

        $handler = $manager->get($stack->getId());
        $request = $this->createMock(ServerRequestInterface::class);
        $handler->handle($request);

        self::assertSame(['manager-terminal'], $collector->entries());
    }
}
