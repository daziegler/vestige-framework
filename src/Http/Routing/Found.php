<?php

declare(strict_types=1);

namespace Vestige\Http\Routing;

use Vestige\Http\Route;

final readonly class Found implements DispatchResultInterface
{
    /**
     * @param array<string, string> $vars
     */
    public function __construct(
        public Route $route,
        public array $vars = [],
    ) {}
}
