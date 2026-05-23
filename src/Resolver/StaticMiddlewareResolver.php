<?php

declare(strict_types=1);

namespace Maduser\Argon\Middleware\Resolver;

use Maduser\Argon\Middleware\Contracts\MiddlewareResolverInterface;
use Maduser\Argon\Middleware\Exception\MiddlewareResolverException;
use Psr\Http\Server\MiddlewareInterface;

final class StaticMiddlewareResolver implements MiddlewareResolverInterface
{
    /** @var array<class-string<MiddlewareInterface>, MiddlewareInterface> */
    private array $instances = [];

    /** @param array<class-string<MiddlewareInterface>, MiddlewareInterface> $instances */
    public function __construct(array $instances)
    {
        foreach ($instances as $class => $instance) {
            if (!$instance instanceof MiddlewareInterface) {
                throw MiddlewareResolverException::notMiddleware($class, $instance);
            }
            $this->instances[$class] = $instance;
        }
    }

    /**
     * @throws MiddlewareResolverException
     */
    #[\Override]
    public function resolve(string $class): MiddlewareInterface
    {
        if (!isset($this->instances[$class])) {
            throw MiddlewareResolverException::notRegistered($class);
        }

        return $this->instances[$class];
    }
}
