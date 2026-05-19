<?php

declare(strict_types=1);

namespace Vestige\Http\Exceptions;

use Throwable;
use Vestige\Http\HttpMethod;
use Vestige\Http\HttpStatus;

final class MethodNotAllowedException extends HttpException
{
    /**
     * @param list<HttpMethod> $allowedMethods
     * @param array<string, string> $headers
     */
    public function __construct(
        array $allowedMethods,
        ?string $message = null,
        ?Throwable $previous = null,
        array $headers = [],
    ) {
        $headers['Allow'] = implode(
            ', ',
            array_map(static fn(HttpMethod $method): string => $method->value, $allowedMethods),
        );
        parent::__construct(HttpStatus::MethodNotAllowed, $message, $previous, $headers);
    }
}
