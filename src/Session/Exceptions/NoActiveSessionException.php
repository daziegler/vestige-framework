<?php

declare(strict_types=1);

namespace Vestige\Session\Exceptions;

use RuntimeException;

final class NoActiveSessionException extends RuntimeException implements SessionExceptionInterface
{
    public static function create(): self
    {
        return new self('No active session: SessionInterface was used outside a handled request (boot-time service or CLI).');
    }
}