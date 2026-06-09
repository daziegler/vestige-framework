<?php

declare(strict_types=1);

/*
 * Intercepts the unqualified header()/headers_sent() calls in ResponseEmitter
 * via PHP's namespace fallback. Loaded from tests/bootstrap.php only.
 */

namespace Vestige\Http;

use Vestige\Tests\Http\Fixtures\HeaderSpy;

function headers_sent(): bool
{
    return HeaderSpy::$headersSent;
}

function header(string $header, bool $replace = true, int $responseCode = 0): void
{
    HeaderSpy::record($header, $replace, $responseCode);
}
