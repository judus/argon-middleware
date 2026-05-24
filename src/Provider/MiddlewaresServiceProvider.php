<?php

declare(strict_types=1);

namespace Maduser\Argon\Middleware\Provider;

use Maduser\Argon\Container\AbstractServiceProvider;
use Maduser\Argon\Container\ArgonContainer;
use Override;

final class MiddlewaresServiceProvider extends AbstractServiceProvider
{
    #[Override]
    public function register(ArgonContainer $container): void
    {
        unset($container);
    }
}
