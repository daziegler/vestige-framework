<?php

declare(strict_types=1);

namespace Vestige\Http;

final readonly class RouteCollection
{
    /** @param list<Route> $routes */
    public function __construct(public array $routes = []) {}

    public function with(Route $route): self
    {
        return new self([...$this->routes, $route]);
    }

    public function merge(self $other): self
    {
        return new self([...$this->routes, ...$other->routes]);
    }
}
