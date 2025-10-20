<?php

declare(strict_types=1);

namespace Maduser\Argon\Middleware;

use Closure;
use Maduser\Argon\Middleware\Contracts\ResultContextInterface;
use Psr\Http\Message\ResponseInterface;

final class ResultContext implements ResultContextInterface
{
    private mixed $result = null;

    public function set(mixed $result): ResultContextInterface
    {
        $this->result = $result;

        return $this;
    }

    public function get(): mixed
    {
        return $this->result;
    }

    public function has(): bool
    {
        return $this->result !== null;
    }

    public function is(string $type): bool
    {
        return $this->result instanceof $type;
    }

    public function isString(): bool
    {
        return is_string($this->result);
    }

    public function isScalar(): bool
    {
        return is_scalar($this->result);
    }

    public function isClosure(): bool
    {
        return $this->result instanceof Closure;
    }

    public function isResponse(): bool
    {
        return $this->result instanceof ResponseInterface;
    }

    public function isArray(): bool
    {
        return is_array($this->result);
    }

    public function isObject(): bool
    {
        return is_object($this->result);
    }

    public function isCallable(): bool
    {
        return is_callable($this->result);
    }
}

