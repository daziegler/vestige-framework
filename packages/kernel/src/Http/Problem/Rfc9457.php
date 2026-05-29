<?php

declare(strict_types=1);

namespace Vestige\Http\Problem;

/**
 * Constants defined by RFC 9457.
 *
 * @see https://datatracker.ietf.org/doc/html/rfc9457
 */
final class Rfc9457
{
    /** Default value for the `type` member when absent. RFC 9457 §3.1.1. */
    public const string DEFAULT_TYPE = 'about:blank';

    /** Media type for JSON problem details. RFC 9457 §3. */
    public const string MEDIA_TYPE_JSON = 'application/problem+json';

    /** Media type for XML problem details. RFC 9457 §3. */
    public const string MEDIA_TYPE_XML = 'application/problem+xml';

    private function __construct() {}
}