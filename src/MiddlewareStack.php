<?php

declare(strict_types=1);

namespace Maduser\Argon\Middleware;

use Maduser\Argon\Middleware\Contracts\MiddlewareStackInterface;
use Psr\Http\Server\MiddlewareInterface;

final readonly class MiddlewareStack implements MiddlewareStackInterface
{
    /**
     * @param list<class-string<MiddlewareInterface>> $middlewares
     */
    public function __construct(
        private array $middlewares = []
    ) {
    }

    /**
     * A deterministic, order-sensitive hash key.
     */
    #[\Override]
    public function getId(): string
    {
        return 'pipeline__' . md5(implode("\0", $this->middlewares));
    }

    /**
     * @return list<class-string<MiddlewareInterface>>
     */
    #[\Override]
    public function toArray(): array
    {
        return $this->middlewares;
    }
}
