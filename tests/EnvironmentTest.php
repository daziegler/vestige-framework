<?php

declare(strict_types=1);

namespace Vestige\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Vestige\Environment;

#[CoversClass(Environment::class)]
final class EnvironmentTest extends TestCase
{
    #[Test]
    public function cases_have_expected_string_values(): void
    {
        self::assertSame('development', Environment::Development->value);
        self::assertSame('production', Environment::Production->value);
        self::assertSame('testing', Environment::Testing->value);
    }
}
