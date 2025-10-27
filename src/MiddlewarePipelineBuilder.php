<?php

declare(strict_types=1);

namespace Maduser\Argon\Middleware;

use Maduser\Argon\Middleware\Contracts\MiddlewareResolverInterface;
use Maduser\Argon\Middleware\Exception\MiddlewarePipelineException;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

final class MiddlewarePipelineBuilder
{
    /** @var array<string, class-string<MiddlewareInterface>> */
    private array $aliases = [];

    /** @var array<string, list<string>> */
    private array $groups = [];

    /** @var list<MiddlewareDefinition> */
    private array $definitions = [];

    private int $verbosity = MiddlewareVerbosity::NORMAL;

    public function __construct(
        private readonly MiddlewareResolverInterface $resolver,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function setVerbosity(int $level): self
    {
        $this->verbosity = $level;
        return $this;
    }

    public function registerAlias(string $name, string $class, bool $overwrite = false): self
    {
        if (!is_subclass_of($class, MiddlewareInterface::class)) {
            throw MiddlewarePipelineException::aliasMustImplement($name, $class);
        }

        if (isset($this->aliases[$name]) && !$overwrite) {
            throw MiddlewarePipelineException::aliasAlreadyDefined($name);
        }

        $this->aliases[$name] = $class;
        return $this;
    }

    /** @param list<string> $middlewareNames */
    public function registerGroup(string $groupName, array $middlewareNames): self
    {
        if (isset($this->groups[$groupName])) {
            throw MiddlewarePipelineException::groupAlreadyDefined($groupName);
        }

        foreach ($middlewareNames as $name) {
            if (!is_string($name)) {
                throw MiddlewarePipelineException::groupContainsNonString($groupName, $name);
            }
        }

        $this->groups[$groupName] = $middlewareNames;
        return $this;
    }

    public function addMiddleware(string $middlewareAliasOrClass, int $priority = 0): self
    {
        $class = $this->resolveAlias($middlewareAliasOrClass);
        $this->definitions[] = new MiddlewareDefinition($class, $priority);
        return $this;
    }

    public function removeMiddleware(string $middlewareAliasOrClass): self
    {
        $class = $this->resolveAlias($middlewareAliasOrClass);
        $this->definitions = array_filter(
            $this->definitions,
            fn(MiddlewareDefinition $def) => $def->class !== $class
        );
        return $this;
    }

    public function addGroup(string $groupName): self
    {
        if (!array_key_exists($groupName, $this->groups)) {
            throw MiddlewarePipelineException::groupNotDefined($groupName);
        }

        foreach ($this->groups[$groupName] as $alias) {
            $this->addMiddleware($alias);
        }

        return $this;
    }

    public function build(?RequestHandlerInterface $finalHandler = null): MiddlewarePipeline
    {
        if ($this->definitions === []) {
            throw MiddlewarePipelineException::emptyPipeline();
        }

        usort($this->definitions, static fn($a, $b) => $b->priority <=> $a->priority);

        $instances = [];
        foreach ($this->definitions as $definition) {
            $instance = $this->resolver->resolve($definition->class);
            $instances[] = $instance;
        }

        return new MiddlewarePipeline(
            middleware: $instances,
            resolver: $this->resolver,
            logger: $this->logger,
            finalHandler: $finalHandler,
            verbosity: $this->verbosity
        );
    }

    private function resolveAlias(string $name): string
    {
        return $this->aliases[$name] ?? $name;
    }
}
