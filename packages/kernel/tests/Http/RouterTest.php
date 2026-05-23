<?php

declare(strict_types=1);

namespace Vestige\Tests\Http;

use League\Container\Container as LeagueContainer;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Vestige\Container\Container;
use Vestige\Http\Exceptions\MethodNotAllowedException;
use Vestige\Http\Exceptions\NotFoundException;
use Vestige\Http\Route;
use Vestige\Http\RouteCollection;
use Vestige\Http\Router;
use Vestige\Http\Routing\FastRouteDispatcher;
use Vestige\Tests\Http\Fixtures\HelloController;
use Vestige\Tests\Http\Fixtures\NameEchoController;

#[CoversClass(Router::class)]
final class RouterTest extends TestCase
{
    #[Test]
    public function matched_route_returns_controller_response(): void
    {
        $container = new Container(new LeagueContainer());
        $container->register(HelloController::class);

        $routes = new RouteCollection([
            Route::get('/hello', HelloController::class),
        ]);
        $router = new Router($container, new FastRouteDispatcher($routes));

        $request = new Psr17Factory()->createServerRequest('GET', '/hello');
        $response = $router->handle($request);

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('Hello', (string) $response->getBody());
    }

    #[Test]
    public function route_vars_become_request_attributes(): void
    {
        $container = new Container(new LeagueContainer());
        $container->register(NameEchoController::class);

        $routes = new RouteCollection([
            Route::get('/hello/{name}', NameEchoController::class),
        ]);
        $router = new Router($container, new FastRouteDispatcher($routes));

        $request = (new Psr17Factory())->createServerRequest('GET', '/hello/world');
        $response = $router->handle($request);

        self::assertSame('world', (string) $response->getBody());
    }

    #[Test]
    public function unmatched_path_throws_not_found(): void
    {
        $container = new Container(new LeagueContainer());
        $routes = new RouteCollection();
        $router = new Router($container, new FastRouteDispatcher($routes));

        $this->expectException(NotFoundException::class);
        $router->handle((new Psr17Factory())->createServerRequest('GET', '/nope'));
    }

    #[Test]
    public function wrong_method_throws_method_not_allowed_with_allowed_methods(): void
    {
        $container = new Container(new LeagueContainer());
        $container->register(HelloController::class);

        $routes = new RouteCollection([
            Route::get('/hello', HelloController::class),
            Route::post('/hello', HelloController::class),
        ]);
        $router = new Router($container, new FastRouteDispatcher($routes));

        try {
            $router->handle((new Psr17Factory())->createServerRequest('DELETE', '/hello'));
            self::fail('Expected MethodNotAllowedException');
        } catch (MethodNotAllowedException $methodNotAllowedException) {
            self::assertSame('GET, POST', $methodNotAllowedException->getHeaders()['Allow']);
        }
    }
}
