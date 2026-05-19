<?php

declare(strict_types=1);

namespace Vestige\Config\Exceptions;

use RuntimeException;

final class InvalidConfigFileException extends RuntimeException implements ConfigExceptionInterface
{
    public static function forNonArrayReturn(string $file): self
    {
        return new self(sprintf('Config file "%s" did not return an array.', $file));
    }
}
