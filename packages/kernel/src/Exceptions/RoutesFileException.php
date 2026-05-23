<?php

declare(strict_types=1);

namespace Vestige\Exceptions;

use LogicException;
use Vestige\Http\RouteCollection;

final class RoutesFileException extends LogicException
{
    public static function missing(string $path): self
    {
        return new self(sprintf(
            'Routes file not found at %s. Create one returning a %s instance.',
            $path,
            RouteCollection::class,
        ));
    }

    public static function invalidReturn(string $path, mixed $returned): self
    {
        return new self(sprintf(
            '%s must return a %s instance, %s returned.',
            $path,
            RouteCollection::class,
            get_debug_type($returned),
        ));
    }
}