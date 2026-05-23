<?php

declare(strict_types=1);

namespace Vestige\Http;

use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use LogicException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Vestige\Container\ContainerInterface;
use Vestige\Http\Exceptions\MethodNotAllowedException;
use Vestige\Http\Exceptions\NotFoundException;
use function FastRoute\simpleDispatcher;

final readonly class Router implements RequestHandlerInterface
{
    private Dispatcher $dispatcher;

    public function __construct(
        private ContainerInterface $container,
        RouteCollection $routes,
    ) {
        $this->dispatcher = self::buildDispatcher($routes);
    }

    private static function buildDispatcher(RouteCollection $routes): Dispatcher
    {
        return simpleDispatcher(static function (RouteCollector $collector) use ($routes): void {
            foreach ($routes->routes as $route) {
                $collector->addRoute($route->method->value, $route->path, $route);
            }
        });
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        /** @var array<int, mixed> $info */
        $info = $this->dispatcher->dispatch(
            $request->getMethod(),
            $request->getUri()->getPath(),
        );

        return match ($info[0]) {
            Dispatcher::NOT_FOUND => throw new NotFoundException(),
            Dispatcher::METHOD_NOT_ALLOWED => $this->throwMethodNotAllowed($info),
            Dispatcher::FOUND => $this->dispatchFound($request, $info),
            default => throw new LogicException('Unexpected dispatcher result.'),
        };
    }

    /**
     * @param array<int, mixed> $info
     */
    private function throwMethodNotAllowed(array $info): never
    {
        /** @var list<string> $allowedMethodNames */
        $allowedMethodNames = $info[1];

        throw new MethodNotAllowedException(
            array_map(static fn(string $name): HttpMethod => HttpMethod::from($name), $allowedMethodNames),
        );
    }

    /**
     * @param array<int, mixed> $info
     */
    private function dispatchFound(ServerRequestInterface $request, array $info): ResponseInterface
    {
        /** @var Route $route */
        $route = $info[1];
        /** @var array<string, string> $vars */
        $vars = $info[2];

        foreach ($vars as $key => $value) {
            $request = $request->withAttribute($key, $value);
        }

        $controller = $this->container->get($route->controller);

        $pipeline = new MiddlewarePipeline(
            $this->container,
            $route->middleware,
            $controller,
        );

        return $pipeline->handle($request);
    }
}
