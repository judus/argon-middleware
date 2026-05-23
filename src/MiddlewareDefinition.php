<?php

declare(strict_types=1);

namespace Maduser\Argon\Middleware;

use Psr\Http\Server\MiddlewareInterface;

final class MiddlewareDefinition
{
    public const DEFAULT_GROUP = '__ungrouped';

    /**
     * @param class-string<MiddlewareInterface> $class
     */
    public function __construct(
        public readonly string $class,
        public readonly int $priority = 0
    ) {
    }
}
