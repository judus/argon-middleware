<?php

declare(strict_types=1);

namespace Tests\Unit;

use InvalidArgumentException;
use Maduser\Argon\Middleware\Contracts\MiddlewareLoaderInterface;
use Maduser\Argon\Middleware\Contracts\MiddlewareResolverInterface;
use Maduser\Argon\Middleware\Factory\RequestHandlerFactory;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

final class RequestHandlerFactoryTest extends TestCase
{
    public function testCreateFromStackRejectsInvalidEntries(): void
    {
        $factory = new RequestHandlerFactory(
            resolver: $this->createMock(MiddlewareResolverInterface::class),
            logger: $this->createMock(LoggerInterface::class),
            loader: $this->createMock(MiddlewareLoaderInterface::class)
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Middleware must be class-string or instance of MiddlewareInterface.');

        $factory->createFromStack([123]);
    }
}
