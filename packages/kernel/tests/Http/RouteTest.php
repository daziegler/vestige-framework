<?php

declare(strict_types=1);

namespace Vestige\Tests\Http;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use stdClass;
use Vestige\Http\Exceptions\InvalidRouteException;
use Vestige\Http\HttpMethod;
use Vestige\Http\Route;
use Vestige\Tests\Http\Fixtures\HelloController;
use Vestige\Tests\Http\Fixtures\HelloMiddleware;

#[CoversClass(Route::class)]
#[CoversClass(InvalidRouteException::class)]
final class RouteTest extends TestCase
{
    #[Test]
    public function create_returns_route_with_valid_inputs(): void
    {
        $route = Route::create(HttpMethod::Get, '/', HelloController::class);

        self::assertSame(HttpMethod::Get, $route->method);
        self::assertSame('/', $route->path);
        self::assertSame(HelloController::class, $route->controller);
        self::assertSame([], $route->middleware);
        self::assertNull($route->name);
    }

    #[Test]
    public function throws_when_controller_does_not_implement_controller_interface(): void
    {
        $this->expectException(InvalidRouteException::class);
        Route::create(HttpMethod::Get, '/', stdClass::class);
    }

    #[Test]
    public function throws_when_middleware_does_not_implement_middleware_interface(): void
    {
        $this->expectException(InvalidRouteException::class);
        Route::create(HttpMethod::Get, '/', HelloController::class, [stdClass::class]);
    }

    #[Test]
    #[DataProvider('factoryCases')]
    public function factory_creates_route_with_matching_method(string $factory, HttpMethod $expected): void
    {
        /** @var Route $route */
        $route = Route::$factory('/', HelloController::class);

        self::assertSame($expected, $route->method);
        self::assertSame('/', $route->path);
        self::assertSame(HelloController::class, $route->controller);
    }

    /** @return iterable<string, array{string, HttpMethod}> */
    public static function factoryCases(): iterable
    {
        yield 'get' => ['get', HttpMethod::Get];
        yield 'post' => ['post', HttpMethod::Post];
        yield 'put' => ['put', HttpMethod::Put];
        yield 'patch' => ['patch', HttpMethod::Patch];
        yield 'delete' => ['delete', HttpMethod::Delete];
        yield 'head' => ['head', HttpMethod::Head];
        yield 'options' => ['options', HttpMethod::Options];
    }

    #[Test]
    public function with_middleware_returns_new_route_with_added_middleware(): void
    {
        $route = Route::create(HttpMethod::Get, '/', HelloController::class);
        $extended = $route->withMiddleware(HelloMiddleware::class);

        self::assertNotSame($route, $extended);
        self::assertSame([], $route->middleware);
        self::assertSame([HelloMiddleware::class], $extended->middleware);
    }

    #[Test]
    public function with_middlewares_appends_multiple_via_varargs(): void
    {
        $route = Route::create(HttpMethod::Get, '/', HelloController::class);
        $extended = $route->withMiddlewares(HelloMiddleware::class, HelloMiddleware::class);

        self::assertSame([HelloMiddleware::class, HelloMiddleware::class], $extended->middleware);
    }

    #[Test]
    public function with_name_returns_new_route_with_name_set(): void
    {
        $route = Route::create(HttpMethod::Get, '/', HelloController::class);
        $named = $route->withName('home');

        self::assertNotSame($route, $named);
        self::assertNull($route->name);
        self::assertSame('home', $named->name);
    }
}
