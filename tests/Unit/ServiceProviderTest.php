<?php

declare(strict_types=1);

namespace Tests\Unit;

use Maduser\Argon\Container\AbstractServiceProvider;
use Maduser\Argon\Middleware\Provider\MiddlewarePipelineServiceProvider;
use PHPUnit\Framework\TestCase;

final class ServiceProviderTest extends TestCase
{
    public function testProvidersAreArgonServiceProviders(): void
    {
        self::assertTrue(is_subclass_of(MiddlewarePipelineServiceProvider::class, AbstractServiceProvider::class));
    }
}
