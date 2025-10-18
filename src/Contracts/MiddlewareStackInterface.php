<?php

declare(strict_types=1);

namespace Maduser\Argon\Middleware\Contracts;

/**
 * Describes an immutable middleware stack representation.
 */
interface MiddlewareStackInterface
{
    /**
     * Deterministic, order-sensitive hash for caching.
     */
    public function getId(): string;

    /**
     * @return list<class-string>
     */
    public function toArray(): array;
}

