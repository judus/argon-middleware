<?php

declare(strict_types=1);

namespace Maduser\Argon\Middleware;

use Maduser\Argon\Middleware\Exception\EmptyMiddlewareChainException;
use Maduser\Argon\Middleware\Contracts\MiddlewareResolverInterface;
use Maduser\Argon\Middleware\Exception\MiddlewareDispatcherException;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

final readonly class MiddlewareDispatcher implements RequestHandlerInterface
{
    /**
     * @param list<class-string<MiddlewareInterface>|MiddlewareInterface> $middleware
     */
    public function __construct(
        private array $middleware,
        private MiddlewareResolverInterface $resolver,
        private ?RequestHandlerInterface $finalHandler,
        private LoggerInterface $logger,
        private int $verbosity = MiddlewareVerbosity::NORMAL,
    ) {
    }

    #[\Override]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->dispatch(0, $request);
    }

    /**
     * @internal Builds the middleware chain recursively without mutable state.
     *
     * @throws EmptyMiddlewareChainException
     */
    public function dispatch(int $index, ServerRequestInterface $request): ResponseInterface
    {
        if (!isset($this->middleware[$index])) {
            if ($this->finalHandler !== null) {
                if ($this->verbosity >= MiddlewareVerbosity::DEBUG) {
                    $this->logger->info('Final handler invoked');
                }

                return $this->finalHandler->handle($request);
            }

            throw new EmptyMiddlewareChainException();
        }

        $entry = $this->middleware[$index];

        if (is_string($entry)) {
            $middleware = $this->resolver->resolve($entry);
        } elseif ($entry instanceof MiddlewareInterface) {
            $middleware = $entry;
        } else {
            throw MiddlewareDispatcherException::invalidEntry($index, $entry);
        }

        if ($this->verbosity >= MiddlewareVerbosity::NORMAL) {
            $this->logger->info('Executing middleware', ['middleware' => $middleware]);
        }

        $nextHandler = new class ($this, $index + 1) implements RequestHandlerInterface {
            public function __construct(
                private MiddlewareDispatcher $dispatcher,
                private int $nextIndex
            ) {
            }

            #[\Override]
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                return $this->dispatcher->dispatch($this->nextIndex, $request);
            }
        };

        return $middleware->process($request, $nextHandler);
    }
}
