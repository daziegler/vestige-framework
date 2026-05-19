<?php

declare(strict_types=1);

namespace Vestige\Tests\Http;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Vestige\Http\HttpMethod;

#[CoversClass(HttpMethod::class)]
final class HttpMethodTest extends TestCase
{
    #[Test]
    public function cases_have_expected_string_values(): void
    {
        self::assertSame('GET', HttpMethod::Get->value);
        self::assertSame('POST', HttpMethod::Post->value);
        self::assertSame('PUT', HttpMethod::Put->value);
        self::assertSame('PATCH', HttpMethod::Patch->value);
        self::assertSame('DELETE', HttpMethod::Delete->value);
        self::assertSame('HEAD', HttpMethod::Head->value);
        self::assertSame('OPTIONS', HttpMethod::Options->value);
    }
}
