<?php

declare(strict_types=1);

namespace Maduser\Argon\Middleware\Exception;

use function get_debug_type;
use function sprintf;

final class MiddlewarePipelineException extends MiddlewareException
{
    public static function aliasMustImplement(string $name, string $class): self
    {
        return new self(sprintf(
            "Alias '%s' must map to a class implementing MiddlewareInterface. Got %s.",
            $name,
            $class
        ));
    }

    public static function aliasAlreadyDefined(string $name): self
    {
        return new self(sprintf(
            "Alias '%s' is already defined.",
            $name
        ));
    }

    public static function groupAlreadyDefined(string $groupName): self
    {
        return new self(sprintf(
            "Group '%s' is already defined.",
            $groupName
        ));
    }

    public static function groupContainsNonString(string $groupName, mixed $value): self
    {
        return new self(sprintf(
            "Group '%s' contains non-string middleware alias. Got %s.",
            $groupName,
            get_debug_type($value)
        ));
    }

    public static function groupNotDefined(string $groupName): self
    {
        return new self(sprintf(
            "Middleware group '%s' is not defined.",
            $groupName
        ));
    }

    public static function emptyPipeline(): self
    {
        return new self('Cannot build a pipeline with no middleware.');
    }
}
