<?php

declare(strict_types=1);

namespace Vestige\Config\Exceptions;

use RuntimeException;

final class DirectoryNotFoundException extends RuntimeException implements ConfigExceptionInterface
{
    public static function forPath(string $path): self
    {
        return new self(sprintf('Config directory "%s" does not exist.', $path));
    }
}
