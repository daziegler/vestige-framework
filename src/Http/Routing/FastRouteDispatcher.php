<?php

declare(strict_types=1);

namespace Vestige\Http\Routing;

use FastRoute\Dispatcher as FastRouteContract;
use FastRoute\RouteCollector;
use LogicException;
use Vestige\Http\HttpMethod;
use Vestige\Http\Route;
use Vestige\Http\RouteCollection;

use function FastRoute\simpleDispatcher;

final readonly class FastRouteDispatcher implements DispatcherInterface
{
    private FastRouteContract $delegate;

    public function __construct(RouteCollection $routes)
    {
        $this->delegate = simpleDispatcher(static function (RouteCollector $collector) use ($routes): void {
            foreach ($routes->routes as $route) {
                $collector->addRoute($route->method->value, $route->path, $route);
            }
        });
    }

    public function dispatch(string $method, string $path): DispatchResultInterface
    {
        /** @var array<int, mixed> $info */
        $info = $this->delegate->dispatch($method, rawurldecode($path));

        return match ($info[0]) {
            FastRouteContract::NOT_FOUND => new NotFound(),
            FastRouteContract::METHOD_NOT_ALLOWED => $this->methodNotAllowed($info),
            FastRouteContract::FOUND => $this->found($info),
            default => throw new LogicException('Unexpected FastRoute dispatcher result.'),
        };
    }

    /**
     * @param array<int, mixed> $info
     */
    private function methodNotAllowed(array $info): MethodNotAllowed
    {
        /** @var list<string> $allowedMethodNames */
        $allowedMethodNames = $info[1];

        return new MethodNotAllowed(
            array_map(static fn(string $name): HttpMethod => HttpMethod::from($name), $allowedMethodNames),
        );
    }

    /**
     * @param array<int, mixed> $info
     */
    private function found(array $info): Found
    {
        /** @var Route $route */
        $route = $info[1];
        /** @var array<string, string> $vars */
        $vars = $info[2];

        return new Found($route, $vars);
    }
}
