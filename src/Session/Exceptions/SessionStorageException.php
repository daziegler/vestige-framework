<?php

declare(strict_types=1);

namespace Vestige\Session\Exceptions;

use RuntimeException;

final class SessionStorageException extends RuntimeException implements SessionExceptionInterface
{
    public static function notWritable(string $dir): self
    {
        return new self(sprintf('Session storage directory "%s" is not writable or could not be created.', $dir));
    }

    public static function writeFailed(string $path): self
    {
        return new self(sprintf('Failed to write session file "%s".', $path));
    }
}
