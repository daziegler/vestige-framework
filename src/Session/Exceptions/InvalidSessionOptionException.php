<?php

declare(strict_types=1);

namespace Vestige\Session\Exceptions;

use InvalidArgumentException;

final class InvalidSessionOptionException extends InvalidArgumentException implements SessionExceptionInterface
{
    public static function forKey(string $key, string $expected, mixed $value): self
    {
        return new self(sprintf('Config "%s" expects %s, got %s.', $key, $expected, get_debug_type($value)));
    }
}
