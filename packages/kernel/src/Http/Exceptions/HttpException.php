<?php

declare(strict_types=1);

namespace Vestige\Http\Exceptions;

use RuntimeException;
use Throwable;
use Vestige\Http\HttpStatus;

abstract class HttpException extends RuntimeException implements HttpExceptionInterface
{
    /** @param array<string, string> $headers */
    public function __construct(
        private readonly HttpStatus $status,
        ?string $message = null,
        ?Throwable $previous = null,
        private readonly array $headers = [],
    ) {
        parent::__construct(
            $message ?? $status->reasonPhrase(),
            $status->value,
            $previous,
        );
    }

    public function getStatusCode(): HttpStatus
    {
        return $this->status;
    }

    /** @return array<string, string> */
    public function getHeaders(): array
    {
        return $this->headers;
    }
}