<?php

declare(strict_types=1);

namespace Vestige\Http\Routing;

use Vestige\Http\HttpMethod;

final readonly class MethodNotAllowed implements DispatchResultInterface
{
    /**
     * @param list<HttpMethod> $allowedMethods
     */
    public function __construct(public array $allowedMethods) {}
}
