<?php

declare(strict_types=1);

namespace Tests\Unit;

use Maduser\Argon\Container\AbstractServiceProvider;
use Maduser\Argon\Middleware\Provider\MiddlewaresServiceProvider;
use Maduser\Argon\Middleware\Provider\RequestHandlerServiceProvider;
use PHPUnit\Framework\TestCase;

final class ServiceProviderTest extends TestCase
{
    public function testProvidersAreArgonServiceProviders(): void
    {
        self::assertTrue(is_subclass_of(MiddlewaresServiceProvider::class, AbstractServiceProvider::class));
        self::assertTrue(is_subclass_of(RequestHandlerServiceProvider::class, AbstractServiceProvider::class));
    }
}
