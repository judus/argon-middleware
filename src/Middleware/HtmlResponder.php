<?php

declare(strict_types=1);

namespace Maduser\Argon\Middleware\Middleware;

use Maduser\Argon\Middleware\Contracts\Middleware\HtmlableInterface;
use Maduser\Argon\Middleware\Contracts\Middleware\HtmlResponderInterface;
use Maduser\Argon\Middleware\Contracts\ResultContextInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

final readonly class HtmlResponder extends AbstractResponder implements HtmlResponderInterface
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

        if (!$context instanceof ResultContextInterface) {
            return $handler->handle($request);
        }

        /** @var HtmlableInterface $result */
        $result = $context->get();

        if ($result instanceof HtmlableInterface) {
            return $this->createResponse($result->toHtml(), 'text/html; charset=UTF-8');
        }

        return $handler->handle($request);
    }
}
