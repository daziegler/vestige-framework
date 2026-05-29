<?php

declare(strict_types=1);

namespace Vestige\Http\Exceptions;

use RuntimeException;

final class HeadersAlreadySentException extends RuntimeException
{
    public static function create(): self
    {
        return new self('Cannot emit response: headers already sent.');
    }
}