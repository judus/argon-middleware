<?php

declare(strict_types=1);

namespace Tests\Unit;

use Maduser\Argon\Container\ArgonContainer;
use Maduser\Argon\Middleware\Exception\MiddlewareException;
use Maduser\Argon\Middleware\Loader\TaggedMiddlewareLoader;
use PHPUnit\Framework\TestCase;

final class TaggedMiddlewareLoaderTest extends TestCase
{
    public function testLoadGroupedWithoutTagThrowsException(): void
    {
        $container = $this->createMock(ArgonContainer::class);
        $container->expects(self::never())->method('getTaggedMeta');

        $loader = new TaggedMiddlewareLoader($container, null);

        $this->expectException(MiddlewareException::class);
        $this->expectExceptionMessage('No tag provided for loading middleware.');

        $loader->loadGrouped();
    }
}
