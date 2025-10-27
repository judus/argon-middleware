<?php

declare(strict_types=1);

namespace Maduser\Argon\Middleware\Exception;

use function get_debug_type;
use function sprintf;

final class MiddlewareResolverException extends MiddlewareException
{
    public static function notMiddleware(string $class, mixed $instance): self
    {
        return new self(sprintf(
            "Resolved instance for '%s' must implement MiddlewareInterface. Got %s.",
            $class,
            get_debug_type($instance)
        ));
    }

    public static function notRegistered(string $class): self
    {
        return new self(sprintf(
            "No middleware registered for class '%s'.",
            $class
        ));
    }
}
