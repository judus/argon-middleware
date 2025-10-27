<?php

declare(strict_types=1);

namespace Maduser\Argon\Middleware\Middleware;

use Maduser\Argon\Middleware\Contracts\Middleware\PlainTextResponderInterface;
use Maduser\Argon\Middleware\Contracts\ResultContextInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

final readonly class PlainTextResponder extends AbstractResponder implements PlainTextResponderInterface
{
    public function __construct(
        ResponseFactoryInterface $responseFactory,
        StreamFactoryInterface $streamFactory,
        ?LoggerInterface $logger = null,
    ) {
        parent::__construct($responseFactory, $streamFactory, $logger);
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $context = $request->getAttribute(ResultContextInterface::class);

        if ($context instanceof ResultContextInterface && $context->isString()) {
            return $this->createResponse((string) $context->get(), 'text/plain; charset=UTF-8');
        }

        return $handler->handle($request);
    }
}
