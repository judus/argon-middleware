<?php

declare(strict_types=1);

namespace Maduser\Argon\Middleware\Contracts\Middleware;

interface HtmlableInterface
{
    public function toHtml(): string;
}
