<?php

declare(strict_types=1);

namespace Maduser\Argon\Middleware\Exception;

use function get_debug_type;
use function sprintf;

final class PipelineStoreException extends MiddlewareException
{
    public static function handlerTypeMismatch(string $pipelineId, mixed $handler): self
    {
        return new self(sprintf(
            'Container service [%s] must be a RequestHandlerInterface. Got %s.',
            $pipelineId,
            get_debug_type($handler)
        ));
    }

    public static function stackNotRegistered(string $pipelineId): self
    {
        return new self(sprintf(
            'Pipeline stack [%s] is not registered in the in-memory store.',
            $pipelineId
        ));
    }
}
