<?php

declare(strict_types=1);

namespace Vestige\Tests\Http;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Vestige\Http\HttpMethod;
use Vestige\Http\Route;
use Vestige\Http\RouteCollection;
use Vestige\Tests\Http\Fixtures\HelloController;

#[CoversClass(RouteCollection::class)]
final class RouteCollectionTest extends TestCase
{
    #[Test]
    public function constructs_empty_by_default(): void
    {
        $collection = new RouteCollection();

        self::assertSame([], $collection->routes);
    }

    #[Test]
    public function with_returns_new_collection_containing_added_route(): void
    {
        $original = new RouteCollection();
        $route = Route::create(HttpMethod::Get, '/', HelloController::class);
        $extended = $original->with($route);

        self::assertNotSame($original, $extended);
        self::assertSame([], $original->routes);
        self::assertSame([$route], $extended->routes);
    }

    #[Test]
    public function merge_combines_routes_from_both_collections(): void
    {
        $route1 = Route::create(HttpMethod::Get, '/a', HelloController::class);
        $route2 = Route::create(HttpMethod::Post, '/b', HelloController::class);
        $a = new RouteCollection([$route1]);
        $b = new RouteCollection([$route2]);

        $merged = $a->merge($b);

        self::assertSame([$route1, $route2], $merged->routes);
    }
}
