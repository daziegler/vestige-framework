<?php

declare(strict_types=1);

namespace Vestige\Exceptions;

use RuntimeException;

final class KernelNotBootedException extends RuntimeException
{
    public static function create(): self
    {
        return new self('Kernel has not been booted. Call boot() before handle().');
    }
}
