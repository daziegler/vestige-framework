<?php

declare(strict_types=1);

namespace Vestige\Config\Exceptions;

use RuntimeException;

final class InvalidKeyPathException extends RuntimeException implements ConfigExceptionInterface
{
    public static function forNonArrayPath(string $key): self
    {
        return new self(sprintf('Config key path "%s" descends into non-array.', $key));
    }
}
