<?php

declare(strict_types=1);

namespace Maduser\Argon\Middleware;

use Closure;
use Maduser\Argon\Middleware\Contracts\ResultContextInterface;
use Psr\Http\Message\ResponseInterface;

final class ResultContext implements ResultContextInterface
{
    private mixed $result = null;

    #[\Override]
    public function set(mixed $result): ResultContextInterface
    {
        $this->result = $result;

        return $this;
    }

    #[\Override]
    public function get(): mixed
    {
        return $this->result;
    }

    #[\Override]
    public function has(): bool
    {
        return $this->result !== null;
    }

    #[\Override]
    public function is(string $type): bool
    {
        return $this->result instanceof $type;
    }

    #[\Override]
    public function isString(): bool
    {
        return is_string($this->result);
    }

    #[\Override]
    public function isScalar(): bool
    {
        return is_scalar($this->result);
    }

    #[\Override]
    public function isClosure(): bool
    {
        return $this->result instanceof Closure;
    }

    #[\Override]
    public function isResponse(): bool
    {
        return $this->result instanceof ResponseInterface;
    }

    #[\Override]
    public function isArray(): bool
    {
        return is_array($this->result);
    }

    #[\Override]
    public function isObject(): bool
    {
        return is_object($this->result);
    }

    #[\Override]
    public function isCallable(): bool
    {
        return is_callable($this->result);
    }
}
