<?php

declare(strict_types=1);

namespace Vestige\Config\Exceptions;

use RuntimeException;

final class KeyNotFoundException extends RuntimeException implements ConfigExceptionInterface
{
    public static function forKey(string $key): self
    {
        return new self(sprintf('Config key "%s" not found.', $key));
    }
}