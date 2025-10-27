<?php

declare(strict_types=1);

namespace Maduser\Argon\Middleware\Factory;

use Maduser\Argon\Middleware\Contracts\MiddlewareLoaderInterface;
use Maduser\Argon\Middleware\Contracts\MiddlewareResolverInterface;
use Maduser\Argon\Middleware\Contracts\PipelineManagerInterface;
use Maduser\Argon\Middleware\Contracts\RequestHandlerFactoryInterface;
use Maduser\Argon\Middleware\Exception\RequestHandlerFactoryException;
use Maduser\Argon\Middleware\MiddlewareDefinition;
use Maduser\Argon\Middleware\MiddlewarePipeline;
use Maduser\Argon\Middleware\MiddlewarePipelineBuilder;
use Override;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

final readonly class RequestHandlerFactory implements RequestHandlerFactoryInterface
{
    public function __construct(
        private MiddlewareResolverInterface $resolver,
        private LoggerInterface $logger,
        private MiddlewareLoaderInterface $loader,
        private ?PipelineManagerInterface $pipelines = null
    ) {
    }

    #[Override]
    public function create(string $cacheKey = 'http_pipeline'): RequestHandlerInterface
    {
        $cached =  $this->pipelines?->get($cacheKey);

        if ($cached instanceof RequestHandlerInterface) {
            return $cached;
        }

        $builder = new MiddlewarePipelineBuilder($this->resolver, $this->logger);

        $groups = $this->loader->loadGrouped();

        foreach ($groups as $groupName => $definitions) {
            foreach ($definitions as $definition) {
                $builder->registerAlias($definition->class, $definition->class, overwrite: true);
            }
            if ($groupName === MiddlewareDefinition::DEFAULT_GROUP) {
                foreach ($definitions as $definition) {
                    $builder->addMiddleware($definition->class, $definition->priority);
                }
            } else {
                $builder->registerGroup($groupName, array_map(fn($d) => $d->class, $definitions));
                $builder->addGroup($groupName);
            }
        }

        return $builder->build();
    }

    /**
     * @param list<class-string<MiddlewareInterface>|MiddlewareInterface> $middleware
     */
    #[Override]
    public function createFromStack(array $middleware): MiddlewarePipeline
    {
        foreach ($middleware as $item) {
            if (!is_string($item) && !$item instanceof MiddlewareInterface) {
                throw RequestHandlerFactoryException::invalidMiddlewareItem($item);
            }
        }

        return new MiddlewarePipeline(
            middleware: $middleware,
            resolver: $this->resolver,
            logger: $this->logger
        );
    }
}
