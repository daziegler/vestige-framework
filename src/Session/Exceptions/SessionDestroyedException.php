<?php

declare(strict_types=1);

namespace Vestige\Session\Exceptions;

use RuntimeException;

final class SessionDestroyedException extends RuntimeException implements SessionExceptionInterface
{
    public static function create(): self
    {
        return new self('Session was destroyed and can no longer be mutated; for logout-then-login use regenerate() and clear() instead.');
    }
}
