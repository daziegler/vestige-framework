<?php

declare(strict_types=1);

namespace Vestige\Http\Routing;

interface DispatcherInterface
{
    public function dispatch(string $method, string $path): DispatchResultInterface;
}
