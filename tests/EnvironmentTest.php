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

    #[Test]
    public function from_globals_reads_app_env(): void
    {
        $_ENV['APP_ENV'] = 'development';

        self::assertSame(Environment::Development, Environment::fromGlobals());
    }

    #[Test]
    public function from_globals_defaults_to_production_when_unset(): void
    {
        unset($_ENV['APP_ENV']);

        self::assertSame(Environment::Production, Environment::fromGlobals());
    }

    #[Test]
    public function from_globals_defaults_to_production_for_unknown_values(): void
    {
        $_ENV['APP_ENV'] = 'staging';

        self::assertSame(Environment::Production, Environment::fromGlobals());
    }

    #[Test]
    public function from_globals_defaults_to_production_for_non_string_values(): void
    {
        $_ENV['APP_ENV'] = ['not', 'a', 'string'];

        self::assertSame(Environment::Production, Environment::fromGlobals());
    }

    protected function tearDown(): void
    {
        unset($_ENV['APP_ENV']);
    }
}
