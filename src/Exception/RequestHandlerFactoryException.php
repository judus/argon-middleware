<?php

declare(strict_types=1);

namespace Maduser\Argon\Middleware\Exception;

use function get_debug_type;
use function sprintf;

final class RequestHandlerFactoryException extends MiddlewareException
{
    public static function invalidMiddlewareItem(mixed $item): self
    {
        return new self(sprintf(
            'Middleware must be class-string or instance of MiddlewareInterface. Got %s.',
            get_debug_type($item)
        ));
    }
}
