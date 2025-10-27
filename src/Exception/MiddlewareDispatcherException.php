<?php

declare(strict_types=1);

namespace Maduser\Argon\Middleware\Exception;

use function get_debug_type;
use function sprintf;

final class MiddlewareDispatcherException extends MiddlewareException
{
    public static function invalidEntry(int $index, mixed $entry): self
    {
        return new self(sprintf(
            'Invalid middleware entry at index %d. Expected class-string or MiddlewareInterface, got %s.',
            $index,
            get_debug_type($entry)
        ));
    }
}
