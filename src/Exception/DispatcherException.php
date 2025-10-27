<?php

declare(strict_types=1);

namespace Maduser\Argon\Middleware\Exception;

use function sprintf;

final class DispatcherException extends MiddlewareException
{
    public static function missingTemplate(string $path): self
    {
        return new self(sprintf(
            'Missing welcome template at %s.',
            $path
        ));
    }
}
