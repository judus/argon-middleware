<?php

declare(strict_types=1);

namespace Tests\Unit;

use Maduser\Argon\Middleware\Contracts\MiddlewareLoaderInterface;
use Maduser\Argon\Middleware\Contracts\MiddlewareResolverInterface;
use Maduser\Argon\Middleware\Factory\RequestHandlerFactory;
use Maduser\Argon\Middleware\Exception\RequestHandlerFactoryException;
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

        $this->expectException(RequestHandlerFactoryException::class);
        $this->expectExceptionMessage('Middleware must be class-string or instance of MiddlewareInterface. Got int.');

        /** @psalm-suppress InvalidArgument Testing runtime validation for invalid middleware entries. */
        $factory->createFromStack([123]);
    }
}
