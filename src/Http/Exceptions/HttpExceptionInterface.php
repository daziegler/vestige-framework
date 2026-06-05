<?php

declare(strict_types=1);

namespace Vestige\Http\Exceptions;

use Throwable;
use Vestige\Http\HttpStatus;

interface HttpExceptionInterface extends Throwable
{
    public function getStatusCode(): HttpStatus;

    /** @return array<string, string> */
    public function getHeaders(): array;
}
