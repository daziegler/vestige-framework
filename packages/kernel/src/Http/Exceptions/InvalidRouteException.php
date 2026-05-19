<?php

declare(strict_types=1);

namespace Vestige\Http\Exceptions;

use InvalidArgumentException;
use Psr\Http\Server\MiddlewareInterface;
use Vestige\Http\ControllerInterface;

final class InvalidRouteException extends InvalidArgumentException
{
    public static function forNonController(string $class): self
    {
        return new self(sprintf(
            'Route controller "%s" does not implement %s.',
            $class,
            ControllerInterface::class,
        ));
    }

    public static function forNonMiddleware(string $class): self
    {
        return new self(sprintf(
            'Route middleware "%s" does not implement %s.',
            $class,
            MiddlewareInterface::class,
        ));
    }
}
