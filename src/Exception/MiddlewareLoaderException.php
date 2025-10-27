<?php

declare(strict_types=1);

namespace Maduser\Argon\Middleware\Exception;

final class MiddlewareLoaderException extends MiddlewareException
{
    public static function missingTag(): self
    {
        return new self('No tag provided for loading middleware.');
    }
}
