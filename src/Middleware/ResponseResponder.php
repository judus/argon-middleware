<?php

declare(strict_types=1);

namespace Maduser\Argon\Middleware\Middleware;

use Maduser\Argon\Middleware\Contracts\Middleware\ResponseResponderInterface;
use Maduser\Argon\Middleware\Contracts\ResultContextInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

final readonly class ResponseResponder implements MiddlewareInterface, ResponseResponderInterface
{
    public function __construct(
        private ?LoggerInterface $logger = null,
    ) {
    }

    #[\Override]
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @psalm-suppress MixedAssignment PSR-7 request attributes are intentionally mixed. */
        $context = $request->getAttribute(ResultContextInterface::class);

        if ($context instanceof ResultContextInterface && $context->is(ResponseInterface::class)) {
            $this->logger?->info(get_class($this) . ' forwards a response');

            /** @var ResponseInterface */
            return $context->get();
        }

        return $handler->handle($request);
    }
}
