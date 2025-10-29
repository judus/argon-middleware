<?php

declare(strict_types=1);

namespace Maduser\Argon\Middleware\Middleware;

use Maduser\Argon\Middleware\Contracts\Middleware\DispatcherInterface;
use Maduser\Argon\Middleware\Contracts\ResultContextInterface;
use Maduser\Argon\Middleware\Exception\DispatcherException;
use Maduser\Argon\Middleware\ResultContext;
use Maduser\Argon\Support\Helper\Html;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

final readonly class Dispatcher implements DispatcherInterface
{
    private const TEMPLATE_PATH = __DIR__ . '/../../resources/argon-prophecy-welcome.html';

    public function __construct(
        private ?LoggerInterface $logger = null,
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $this->logger?->info('DispatcherMiddleware executing dispatch()');

        $request = $this->dispatch($request);

        return $handler->handle($request);
    }

    public function dispatch(ServerRequestInterface $request): ServerRequestInterface
    {
        $this->logger?->info('Dispatching placeholder logic');

        $html = $this->getPlaceholderHtml();

        $context = $request->getAttribute(ResultContextInterface::class);
        if (!$context instanceof ResultContextInterface) {
            $context = new ResultContext();
        }

        $context->set(Html::create($html, [
            'argonDispatcher' => '\\' . DispatcherInterface::class,
            'customDispatcher' => '\YourApp\YourDispatcher::class',
        ]));

        return $request->withAttribute(ResultContextInterface::class, $context);
    }

    private function getPlaceholderHtml(): string
    {
        if (!file_exists(self::TEMPLATE_PATH)) {
            // This throw is only reachable if deployment is broken and the template file is missing
            // @codeCoverageIgnoreStart
            throw DispatcherException::missingTemplate(self::TEMPLATE_PATH);
            // @codeCoverageIgnoreEnd
        }

        return file_get_contents(self::TEMPLATE_PATH);
    }
}
