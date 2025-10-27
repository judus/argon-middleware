<?php

namespace Maduser\Argon\Middleware\Resolver;

use Maduser\Argon\Middleware\Contracts\MiddlewareResolverInterface;
use Maduser\Argon\Middleware\Exception\MiddlewareResolverException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Server\MiddlewareInterface;

final readonly class ContainerMiddlewareResolver implements MiddlewareResolverInterface
{
    public function __construct(
        private ContainerInterface $container
    ) {}

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws MiddlewareResolverException
     */
    public function resolve(string $class): MiddlewareInterface
    {
        $instance = $this->container->get($class);

        if (!$instance instanceof MiddlewareInterface) {
            throw MiddlewareResolverException::notMiddleware($class, $instance);
        }

        return $instance;
    }
}
