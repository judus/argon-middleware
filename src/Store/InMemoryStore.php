<?php

namespace Maduser\Argon\Middleware\Store;

use Maduser\Argon\Container\ArgonContainer;
use Maduser\Argon\Middleware\Contracts\MiddlewareStackInterface;
use Maduser\Argon\Middleware\Contracts\PipelineStoreInterface;
use Maduser\Argon\Middleware\MiddlewarePipeline;
use Maduser\Argon\Middleware\MiddlewareVerbosity;
use Maduser\Argon\Middleware\Resolver\ContainerMiddlewareResolver;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use RuntimeException;

class InMemoryStore implements PipelineStoreInterface
{
    /** @var array<string, MiddlewareStackInterface> */
    private array $stacks = [];

    private ContainerMiddlewareResolver $resolver;

    private LoggerInterface $logger;

    public function __construct(
        ?ArgonContainer $container = null,
        ?LoggerInterface $logger = null
    ) {
        $container ??= new ArgonContainer();
        $this->resolver = new ContainerMiddlewareResolver(container: $container);
        $this->logger = $logger ?? new NullLogger();
    }

    public function get(MiddlewareStackInterface|string $keyOrStack): RequestHandlerInterface
    {
        if ($keyOrStack instanceof MiddlewareStackInterface) {
            $stack = $keyOrStack;
        } else {
            $stack = $this->stacks[$keyOrStack] ?? null;
            if ($stack === null) {
                throw new RuntimeException(sprintf(
                    'Pipeline stack [%s] is not registered in the in-memory store.',
                    $keyOrStack
                ));
            }
        }

        return new MiddlewarePipeline(
            middleware: $stack->toArray(),
            resolver: $this->resolver,
            logger: $this->logger,
            finalHandler: null,
            verbosity: MiddlewareVerbosity::NORMAL
        );
    }

    public function register(MiddlewareStackInterface $stack): PipelineStoreInterface
    {
        $this->stacks[$stack->getId()] = $stack;

        return $this;
    }
}
