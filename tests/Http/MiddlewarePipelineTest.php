<?php

declare(strict_types=1);

namespace Vestige\Tests\Http;

use League\Container\Container as LeagueContainer;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Vestige\Container\Container;
use Vestige\Http\MiddlewarePipeline;
use Vestige\Tests\Http\Fixtures\HeaderAddingMiddleware;
use Vestige\Tests\Http\Fixtures\HelloController;
use Vestige\Tests\Http\Fixtures\ShortCircuitMiddleware;
use Vestige\Tests\Http\Fixtures\ThrowingMiddleware;

#[CoversClass(MiddlewarePipeline::class)]
final class MiddlewarePipelineTest extends TestCase
{
    #[Test]
    public function empty_pipeline_returns_final_handlers_response(): void
    {
        $container = new Container(new LeagueContainer());
        $finalHandler = new HelloController();
        $pipeline = new MiddlewarePipeline($container, [], $finalHandler);

        $request = (new Psr17Factory())->createServerRequest('GET', '/');
        $response = $pipeline->handle($request);

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('Hello', (string) $response->getBody());
    }

    #[Test]
    public function single_middleware_processes_response(): void
    {
        $container = new Container(new LeagueContainer());
        $container->register(HeaderAddingMiddleware::class);

        $pipeline = new MiddlewarePipeline(
            $container,
            [HeaderAddingMiddleware::class],
            new HelloController(),
        );

        $request = (new Psr17Factory())->createServerRequest('GET', '/');
        $response = $pipeline->handle($request);

        self::assertSame('hit', $response->getHeaderLine('X-Test'));
    }

    #[Test]
    public function short_circuit_middleware_prevents_resolution_of_later_middleware(): void
    {
        $container = new Container(new LeagueContainer());
        $container->register(ShortCircuitMiddleware::class);
        $container->register(ThrowingMiddleware::class);

        $pipeline = new MiddlewarePipeline(
            $container,
            [ShortCircuitMiddleware::class, ThrowingMiddleware::class],
            new HelloController(),
        );

        $request = (new Psr17Factory())->createServerRequest('GET', '/');
        $response = $pipeline->handle($request);

        self::assertSame(403, $response->getStatusCode());
    }
}
