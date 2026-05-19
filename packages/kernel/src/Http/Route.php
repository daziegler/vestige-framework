<?php

declare(strict_types=1);

namespace Vestige\Http;

use Psr\Http\Server\MiddlewareInterface;
use Vestige\Http\Exceptions\InvalidRouteException;

final readonly class Route
{
    /**
     * @param class-string<ControllerInterface>       $controller
     * @param list<class-string<MiddlewareInterface>> $middleware
     */
    private function __construct(
        public HttpMethod $method,
        public string $path,
        public string $controller,
        public array $middleware = [],
        public ?string $name = null,
    ) {}

    /** @param class-string<ControllerInterface> $controller */
    public static function get(string $path, string $controller): self
    {
        return self::create(HttpMethod::Get, $path, $controller);
    }

    /**
     * @param class-string        $controller
     * @param array<class-string> $middleware
     */
    public static function create(
        HttpMethod $method,
        string $path,
        string $controller,
        array $middleware = [],
        ?string $name = null,
    ): self {
        if (is_a($controller, ControllerInterface::class, true) === false) {
            throw InvalidRouteException::forNonController($controller);
        }

        foreach ($middleware as $middlewareClass) {
            if (is_a($middlewareClass, MiddlewareInterface::class, true) === false) {
                throw InvalidRouteException::forNonMiddleware($middlewareClass);
            }
        }

        /** @var class-string<MiddlewareInterface>[] $middleware */
        return new self($method, $path, $controller, array_values($middleware), $name);
    }

    /** @param class-string<ControllerInterface> $controller */
    public static function post(string $path, string $controller): self
    {
        return self::create(HttpMethod::Post, $path, $controller);
    }

    /** @param class-string<ControllerInterface> $controller */
    public static function put(string $path, string $controller): self
    {
        return self::create(HttpMethod::Put, $path, $controller);
    }

    /** @param class-string<ControllerInterface> $controller */
    public static function patch(string $path, string $controller): self
    {
        return self::create(HttpMethod::Patch, $path, $controller);
    }

    /** @param class-string<ControllerInterface> $controller */
    public static function delete(string $path, string $controller): self
    {
        return self::create(HttpMethod::Delete, $path, $controller);
    }

    /** @param class-string<ControllerInterface> $controller */
    public static function head(string $path, string $controller): self
    {
        return self::create(HttpMethod::Head, $path, $controller);
    }

    /** @param class-string<ControllerInterface> $controller */
    public static function options(string $path, string $controller): self
    {
        return self::create(HttpMethod::Options, $path, $controller);
    }

    /** @param class-string<MiddlewareInterface> ...$middleware */
    public function withMiddleware(string ...$middleware): self
    {
        return self::create(
            $this->method,
            $this->path,
            $this->controller,
            [...$this->middleware, ...$middleware],
            $this->name,
        );
    }

    public function withName(string $name): self
    {
        return self::create(
            $this->method,
            $this->path,
            $this->controller,
            $this->middleware,
            $name,
        );
    }
}
