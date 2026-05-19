<?php

declare(strict_types=1);

namespace Vestige\Http\Exceptions;

use Throwable;
use Vestige\Http\HttpStatus;

final class ForbiddenException extends HttpException
{
    /** @param array<string, string> $headers */
    public function __construct(
        ?string $message = null,
        ?Throwable $previous = null,
        array $headers = [],
    ) {
        parent::__construct(HttpStatus::Forbidden, $message, $previous, $headers);
    }
}