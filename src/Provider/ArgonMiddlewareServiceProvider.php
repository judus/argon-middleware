<?php

declare(strict_types=1);

namespace Maduser\Argon\Middleware\Provider;

use Maduser\Argon\Container\AbstractServiceProvider;
use Maduser\Argon\Container\ArgonContainer;
use Maduser\Argon\Container\Exceptions\ContainerException;
use Maduser\Argon\Middleware\Factory\RequestHandlerFactory;
use Maduser\Argon\Middleware\Contracts\MiddlewareLoaderInterface;
use Maduser\Argon\Middleware\Contracts\MiddlewarePipelineCacheInterface;
use Maduser\Argon\Middleware\Contracts\MiddlewareResolverInterface;
use Maduser\Argon\Middleware\Contracts\PipelineManagerInterface;
use Maduser\Argon\Middleware\Contracts\PipelineStoreInterface;
use Maduser\Argon\Middleware\Contracts\RequestHandlerFactoryInterface;
use Maduser\Argon\Middleware\Loader\TaggedMiddlewareLoader;
use Maduser\Argon\Middleware\MiddlewarePipeline;
use Maduser\Argon\Middleware\MiddlewarePipelineCache;
use Maduser\Argon\Middleware\PipelineManager;
use Maduser\Argon\Middleware\Resolver\ContainerMiddlewareResolver;
use Maduser\Argon\Middleware\ResultContext;
use Maduser\Argon\Middleware\Store\ContainerStore;
use Psr\Http\Server\RequestHandlerInterface;

class ArgonMiddlewareServiceProvider extends AbstractServiceProvider
{
    private const DEFAULT_MIDDLEWARE_TAG = 'middleware.http';

    /**
     * @throws ContainerException
     */
    public function register(ArgonContainer $container): void
    {
        $container->set(PipelineManager::class, args: [
            'store' => PipelineStoreInterface::class
        ]);

        $container->set(PipelineManagerInterface::class, PipelineManager::class, [
            'store' => PipelineStoreInterface::class,
        ])
            ->tag(['middleware.manager']);

        $container->set(PipelineStoreInterface::class, ContainerStore::class)
            ->tag(['middleware.store']);

        $container->set(MiddlewareLoaderInterface::class, TaggedMiddlewareLoader::class, [
            'tag' => $container->getParameters()->get('middleware.tag', self::DEFAULT_MIDDLEWARE_TAG),
        ])
            ->tag(['middleware.loader']);

        $container->set(MiddlewarePipelineCacheInterface::class, MiddlewarePipelineCache::class)
            ->tag(['middleware.cache']);

        $container->set(MiddlewareResolverInterface::class, ContainerMiddlewareResolver::class)
            ->tag(['middleware.resolver']);

        /**
         * Override the default middleware pipeline
         */
        $container->set(RequestHandlerFactory::class);

        $container->set(RequestHandlerFactoryInterface::class, RequestHandlerFactory::class)
            ->tag(['middleware.request_handler_factory']);

        $container->set(RequestHandlerInterface::class, MiddlewarePipeline::class)
            ->factory(RequestHandlerFactoryInterface::class, 'create')
            ->tag(['middleware.request_handler']);
    }
}
