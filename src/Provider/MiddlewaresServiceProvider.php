<?php

declare(strict_types=1);

namespace Maduser\Argon\Middleware\Provider;

use Maduser\Argon\Container\AbstractServiceProvider;
use Maduser\Argon\Container\ArgonContainer;
use Maduser\Argon\Container\Exceptions\ContainerException;
use Maduser\Argon\Middleware\Contracts\Middleware\DispatcherInterface;
use Maduser\Argon\Middleware\Contracts\Middleware\HtmlResponderInterface;
use Maduser\Argon\Middleware\Contracts\Middleware\JsonResponderInterface;
use Maduser\Argon\Middleware\Contracts\Middleware\PlainTextResponderInterface;
use Maduser\Argon\Middleware\Contracts\Middleware\ResponseResponderInterface;
use Maduser\Argon\Middleware\Middleware\Dispatcher;
use Maduser\Argon\Middleware\Middleware\HtmlResponder;
use Maduser\Argon\Middleware\Middleware\JsonResponder;
use Maduser\Argon\Middleware\Middleware\PlainTextResponder;
use Maduser\Argon\Middleware\Middleware\ResponseResponder;
use Override;

class MiddlewaresServiceProvider extends AbstractServiceProvider
{
    /**
     * @throws ContainerException
     */
    #[Override]
    public function register(ArgonContainer $container): void
    {


        $container->set(JsonResponderInterface::class, JsonResponder::class)
            ->tag(['middleware.http' => ['priority' => 5800, 'group' => ['api', 'web']]]);

        $container->set(HtmlResponderInterface::class, HtmlResponder::class)
            ->tag(['middleware.http' => ['priority' => 5600, 'group' => 'web']]);

        $container->set(PlainTextResponderInterface::class, PlainTextResponder::class)
            ->tag(['middleware.http' => ['priority' => 5400, 'group' => 'web']]);

        $container->set(ResponseResponderInterface::class, ResponseResponder::class)
            ->tag(['middleware.http' => ['priority' => 5200, 'group' => ['api', 'web']]]);
    }
}
