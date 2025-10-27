<?php

declare(strict_types=1);

namespace Maduser\Argon\Middleware\Middleware;

use JsonException;
use JsonSerializable;
use Maduser\Argon\Middleware\Contracts\Middleware\JsonResponderInterface;
use Maduser\Argon\Middleware\Contracts\ResultContextInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

final readonly class JsonResponder extends AbstractResponder implements JsonResponderInterface
{
    public function __construct(
        ResponseFactoryInterface $responseFactory,
        StreamFactoryInterface $streamFactory,
        ?LoggerInterface $logger = null,
    ) {
        parent::__construct($responseFactory, $streamFactory, $logger);
    }

    /**
     * @throws JsonException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $context = $request->getAttribute(ResultContextInterface::class);

        if (!$context instanceof ResultContextInterface) {
            return $handler->handle($request);
        }

        /** @var array|JsonSerializable $raw */
        $raw = $context->get();

        if ($context->isArray() || $raw instanceof JsonSerializable) {
            /** @var array|string|null $data */
            $data = $raw instanceof JsonSerializable
                ? $raw->jsonSerialize()
                : $raw;

            $json = json_encode($data, JSON_THROW_ON_ERROR);

            return $this->createResponse($json, 'application/json; charset=UTF-8');
        }

        return $handler->handle($request);
    }
}
