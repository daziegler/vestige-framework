<?php

declare(strict_types=1);

namespace Vestige\Tests\Http\Fixtures;

final class HeaderSpy
{
    public static bool $headersSent = false;

    /** @var list<array{header: string, replace: bool, statusCode: int}> */
    public static array $headers = [];

    public static function record(string $header, bool $replace, int $statusCode): void
    {
        self::$headers[] = ['header' => $header, 'replace' => $replace, 'statusCode' => $statusCode];
    }

    public static function reset(): void
    {
        self::$headersSent = false;
        self::$headers = [];
    }
}
