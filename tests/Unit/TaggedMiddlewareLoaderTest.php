<?php

declare(strict_types=1);

namespace Tests\Unit;

use Maduser\Argon\Container\ArgonContainer;
use Maduser\Argon\Middleware\Exception\MiddlewareLoaderException;
use Maduser\Argon\Middleware\Loader\TaggedMiddlewareLoader;
use Maduser\Argon\Middleware\MiddlewareDefinition;
use PHPUnit\Framework\TestCase;

final class TaggedMiddlewareLoaderTest extends TestCase
{
    public function testLoadGroupedWithoutTagThrowsException(): void
    {
        $container = $this->createMock(ArgonContainer::class);
        $container->expects(self::never())->method('getTaggedMeta');

        $loader = new TaggedMiddlewareLoader($container, null);

        $this->expectException(MiddlewareLoaderException::class);
        $this->expectExceptionMessage('No tag provided for loading middleware.');

        $loader->loadGrouped();
    }

    public function testLoadReturnsDefinitionsWithPriorities(): void
    {
        $container = $this->createMock(ArgonContainer::class);
        $container->expects(self::once())
            ->method('getTaggedMeta')
            ->with('middleware')
            ->willReturn([
                'App\\FooMiddleware' => ['priority' => 5],
                'App\\BarMiddleware' => [],
            ]);

        $loader = new TaggedMiddlewareLoader($container, 'middleware');

        $definitions = $loader->load();

        self::assertCount(2, $definitions);
        self::assertSame('App\\FooMiddleware', $definitions[0]->class);
        self::assertSame(5, $definitions[0]->priority);
        self::assertSame('App\\BarMiddleware', $definitions[1]->class);
        self::assertSame(0, $definitions[1]->priority);
    }

    public function testLoadGroupedOrganisesDefinitionsByGroup(): void
    {
        $container = $this->createMock(ArgonContainer::class);
        $container->expects(self::once())
            ->method('getTaggedMeta')
            ->with('middleware')
            ->willReturn([
                'App\\AuthMiddleware' => ['group' => 'api', 'priority' => 1],
                'App\\LogMiddleware' => ['group' => ['api', 'web']],
                'App\\ExplicitDefault' => ['group' => []],
                'App\\FallbackMiddleware' => [],
            ]);

        $loader = new TaggedMiddlewareLoader($container, 'middleware');

        $grouped = $loader->loadGrouped();

        self::assertArrayHasKey('api', $grouped);
        self::assertArrayHasKey('web', $grouped);
        self::assertArrayHasKey(MiddlewareDefinition::DEFAULT_GROUP, $grouped);

        self::assertSame('App\\AuthMiddleware', $grouped['api'][0]->class);
        self::assertSame(1, $grouped['api'][0]->priority);

        self::assertSame('App\\LogMiddleware', $grouped['api'][1]->class);
        self::assertSame('App\\LogMiddleware', $grouped['web'][0]->class);

        self::assertSame('App\\ExplicitDefault', $grouped[MiddlewareDefinition::DEFAULT_GROUP][0]->class);
        self::assertSame('App\\FallbackMiddleware', $grouped[MiddlewareDefinition::DEFAULT_GROUP][1]->class);
    }
}
