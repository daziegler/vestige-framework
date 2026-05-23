<?php

declare(strict_types=1);

namespace Vestige\Tests\Http\Routing;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Vestige\Http\HttpMethod;
use Vestige\Http\Route;
use Vestige\Http\RouteCollection;
use Vestige\Http\Routing\FastRouteDispatcher;
use Vestige\Http\Routing\Found;
use Vestige\Http\Routing\MethodNotAllowed;
use Vestige\Http\Routing\NotFound;
use Vestige\Tests\Http\Fixtures\HelloController;

#[CoversClass(FastRouteDispatcher::class)]
#[CoversClass(Found::class)]
#[CoversClass(NotFound::class)]
#[CoversClass(MethodNotAllowed::class)]
final class FastRouteDispatcherTest extends TestCase
{
    #[Test]
    public function dispatches_found_with_route_and_vars(): void
    {
        $route = Route::get('/hello/{name}', HelloController::class);
        $dispatcher = new FastRouteDispatcher(new RouteCollection([$route]));

        $result = $dispatcher->dispatch('GET', '/hello/world');

        self::assertInstanceOf(Found::class, $result);
        self::assertSame($route, $result->route);
        self::assertSame(['name' => 'world'], $result->vars);
    }

    #[Test]
    public function dispatches_not_found_for_unmatched_path(): void
    {
        $dispatcher = new FastRouteDispatcher(new RouteCollection());

        $result = $dispatcher->dispatch('GET', '/nope');

        self::assertInstanceOf(NotFound::class, $result);
    }

    #[Test]
    public function dispatches_method_not_allowed_with_httpmethod_list(): void
    {
        $dispatcher = new FastRouteDispatcher(new RouteCollection([
            Route::get('/hello', HelloController::class),
            Route::post('/hello', HelloController::class),
        ]));

        $result = $dispatcher->dispatch('DELETE', '/hello');

        self::assertInstanceOf(MethodNotAllowed::class, $result);
        self::assertEquals([HttpMethod::Get, HttpMethod::Post], $result->allowedMethods);
    }
}
