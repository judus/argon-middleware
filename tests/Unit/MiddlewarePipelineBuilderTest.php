<?php

declare(strict_types=1);

namespace Tests\Unit;

use Maduser\Argon\Middleware\Contracts\MiddlewareResolverInterface;
use Maduser\Argon\Middleware\Exception\MiddlewareException;
use Maduser\Argon\Middleware\Exception\MiddlewarePipelineException;
use Maduser\Argon\Middleware\MiddlewarePipelineBuilder;
use Maduser\Argon\Middleware\MiddlewareVerbosity;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\NullLogger;

final class MiddlewarePipelineBuilderTest extends TestCase
{
    public function testBuildInvokesResolverAndCreatesPipeline(): void
    {
        $middleware = new class implements MiddlewareInterface {
            public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
            {
                return $handler->handle($request);
            }
        };

        $resolver = $this->createMock(MiddlewareResolverInterface::class);
        $resolver->expects(self::once())
            ->method('resolve')
            ->with(TestMiddleware::class)
            ->willReturn($middleware);

        $response = $this->createMock(ResponseInterface::class);

        $finalHandler = new class($response) implements RequestHandlerInterface {
            public function __construct(private ResponseInterface $response)
            {
            }

            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                return $this->response;
            }
        };

        $builder = new MiddlewarePipelineBuilder($resolver, new NullLogger());
        $builder->setVerbosity(MiddlewareVerbosity::DEBUG);
        $builder->addMiddleware(TestMiddleware::class);

        $pipeline = $builder->build($finalHandler);

        $request = $this->createMock(ServerRequestInterface::class);

        self::assertSame($response, $pipeline->handle($request));
    }

    public function testRegisterAliasRequiresMiddlewareInterface(): void
    {
        $builder = new MiddlewarePipelineBuilder(
            $this->createMock(MiddlewareResolverInterface::class),
            new NullLogger()
        );

        $this->expectException(MiddlewarePipelineException::class);
        $this->expectExceptionMessage("Alias 'foo' must map to a class implementing MiddlewareInterface. Got stdClass.");

        $builder->registerAlias('foo', \stdClass::class);
    }

    public function testRegisterAliasPreventsDuplicates(): void
    {
        $builder = new MiddlewarePipelineBuilder(
            $this->createMock(MiddlewareResolverInterface::class),
            new NullLogger()
        );

        $builder->registerAlias('foo', TestMiddleware::class);

        $this->expectException(MiddlewarePipelineException::class);
        $this->expectExceptionMessage("Alias 'foo' is already defined.");

        $builder->registerAlias('foo', TestMiddleware::class);
    }

    public function testRegisterGroupPreventsDuplicates(): void
    {
        $builder = new MiddlewarePipelineBuilder(
            $this->createMock(MiddlewareResolverInterface::class),
            new NullLogger()
        );

        $builder->registerGroup('api', []);

        $this->expectException(MiddlewarePipelineException::class);
        $this->expectExceptionMessage("Group 'api' is already defined.");

        $builder->registerGroup('api', []);
    }

    public function testRegisterGroupRejectsNonStringEntries(): void
    {
        $builder = new MiddlewarePipelineBuilder(
            $this->createMock(MiddlewareResolverInterface::class),
            new NullLogger()
        );

        $this->expectException(MiddlewarePipelineException::class);
        $this->expectExceptionMessage("Group 'api' contains non-string middleware alias. Got int.");

        $builder->registerGroup('api', ['valid', 123]);
    }

    public function testAddGroupRequiresExistingDefinition(): void
    {
        $builder = new MiddlewarePipelineBuilder(
            $this->createMock(MiddlewareResolverInterface::class),
            new NullLogger()
        );

        $this->expectException(MiddlewarePipelineException::class);
        $this->expectExceptionMessage("Middleware group 'missing' is not defined.");

        $builder->addGroup('missing');
    }

    public function testBuildWithNoDefinitionsThrowsException(): void
    {
        $builder = new MiddlewarePipelineBuilder(
            $this->createMock(MiddlewareResolverInterface::class),
            new NullLogger()
        );

        $this->expectException(MiddlewarePipelineException::class);
        $this->expectExceptionMessage('Cannot build a pipeline with no middleware.');

        $builder->build();
    }

    public function testRemoveMiddlewareEliminatesDefinition(): void
    {
        $builder = new MiddlewarePipelineBuilder(
            $this->createMock(MiddlewareResolverInterface::class),
            new NullLogger()
        );

        $builder->addMiddleware(TestMiddleware::class);
        $builder->removeMiddleware(TestMiddleware::class);

        $this->expectException(MiddlewarePipelineException::class);
        $this->expectExceptionMessage('Cannot build a pipeline with no middleware.');

        $builder->build();
    }
}

final class TestMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        throw new MiddlewareException('This stub should never be called directly.');
    }
}
