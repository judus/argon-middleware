<?php

declare(strict_types=1);

namespace Tests\Integration;

use Maduser\Argon\Container\ArgonContainer;
use Maduser\Argon\Middleware\Exception\MiddlewareException;
use Maduser\Argon\Middleware\Loader\TaggedMiddlewareLoader;
use Maduser\Argon\Middleware\MiddlewareDefinition;
use PHPUnit\Framework\TestCase;
use Tests\Integration\Fixtures\EmptyGroupMiddleware;
use Tests\Integration\Fixtures\LogCollector;
use Tests\Integration\Fixtures\MissingGroupMiddleware;
use Tests\Integration\Fixtures\RecordingMiddleware;
use Tests\Integration\Fixtures\TerminalMiddleware;

final class TaggedMiddlewareLoaderIntegrationTest extends TestCase
{
    public function testLoadReturnsDefinitionsWithPriorities(): void
    {
        $container = new ArgonContainer();
        $tag = 'test.middleware';

        $container->set(RecordingMiddleware::class, fn() => new RecordingMiddleware(new LogCollector(), 'a'))
            ->tag([$tag => ['priority' => 5, 'group' => 'alpha']]);

        $container->set(TerminalMiddleware::class, fn() => new TerminalMiddleware(new LogCollector(), 'b'))
            ->tag([$tag => ['priority' => 2, 'group' => ['alpha', 'beta']]]);

        $container->set(MissingGroupMiddleware::class, fn() => new MissingGroupMiddleware(new LogCollector()))
            ->tag([$tag => ['priority' => 1]]);

        $loader = new TaggedMiddlewareLoader($container, $tag);

        $definitions = $loader->load();
        self::assertSame(
            [
                RecordingMiddleware::class => 5,
                TerminalMiddleware::class => 2,
                MissingGroupMiddleware::class => 1,
            ],
            $this->extractPriorities($definitions)
        );
    }

    public function testLoadGroupedOrganisesDefinitionsByGroupMetadata(): void
    {
        $container = new ArgonContainer();
        $tag = 'test.middleware';

        $container->set(RecordingMiddleware::class, fn() => new RecordingMiddleware(new LogCollector(), 'a'))
            ->tag([$tag => ['priority' => 5, 'group' => 'alpha']]);

        $container->set(TerminalMiddleware::class, fn() => new TerminalMiddleware(new LogCollector(), 'b'))
            ->tag([$tag => ['priority' => 2, 'group' => ['alpha', 'beta']]]);

        $container->set(EmptyGroupMiddleware::class, fn() => new EmptyGroupMiddleware(new LogCollector()))
            ->tag([$tag => ['priority' => 0, 'group' => []]]);

        $container->set(MissingGroupMiddleware::class, fn() => new MissingGroupMiddleware(new LogCollector()))
            ->tag([$tag => ['priority' => 1]]);

        $loader = new TaggedMiddlewareLoader($container, $tag);

        $grouped = $loader->loadGrouped();

        self::assertArrayHasKey('alpha', $grouped);
        self::assertArrayHasKey('beta', $grouped);
        self::assertArrayHasKey(MiddlewareDefinition::DEFAULT_GROUP, $grouped);

        self::assertSame(
            [RecordingMiddleware::class, TerminalMiddleware::class],
            $this->extractClasses($grouped['alpha'])
        );

        self::assertSame(
            [TerminalMiddleware::class],
            $this->extractClasses($grouped['beta'])
        );

        self::assertSame(
            [EmptyGroupMiddleware::class, MissingGroupMiddleware::class],
            $this->extractClasses($grouped[MiddlewareDefinition::DEFAULT_GROUP])
        );
    }

    public function testLoaderWithoutTagThrowsException(): void
    {
        $container = new ArgonContainer();
        $loader = new TaggedMiddlewareLoader($container);

        $this->expectException(MiddlewareException::class);
        $this->expectExceptionMessage('No tag provided for loading middleware.');

        $loader->load();
    }

    /**
     * @param list<MiddlewareDefinition> $definitions
     * @return array<string, int>
     */
    private function extractPriorities(array $definitions): array
    {
        $map = [];
        foreach ($definitions as $definition) {
            $map[$definition->class] = $definition->priority;
        }

        return $map;
    }

    /**
     * @param list<MiddlewareDefinition> $definitions
     * @return list<string>
     */
    private function extractClasses(array $definitions): array
    {
        return array_map(static fn(MiddlewareDefinition $definition) => $definition->class, $definitions);
    }
}
