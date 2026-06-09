<?php

declare(strict_types=1);

namespace Vestige\Tests\Session;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Vestige\Config\Config;
use Vestige\Http\SameSite;
use Vestige\Session\Exceptions\InvalidSessionOptionException;
use Vestige\Session\SessionOptions;

#[CoversClass(SessionOptions::class)]
final class SessionOptionsTest extends TestCase
{
    #[Test]
    public function defaults_apply_with_empty_config(): void
    {
        $options = SessionOptions::fromConfig(new Config([]));

        self::assertSame('vestige_session', $options->cookieName);
        self::assertSame('/', $options->cookiePath);
        self::assertNull($options->cookieDomain);
        self::assertTrue($options->cookieSecure);
        self::assertTrue($options->cookieHttpOnly);
        self::assertSame(SameSite::Lax, $options->cookieSameSite);
        self::assertSame(7200, $options->lifetime);
        self::assertSame(sys_get_temp_dir() . '/vestige_sessions', $options->storageDir);
        self::assertSame(100, $options->gcDivisor);
    }

    #[Test]
    public function config_values_override_defaults(): void
    {
        $config = new Config([
            'session' => [
                'cookie' => [
                    'name' => 'app_sid',
                    'path' => '/app',
                    'domain' => 'example.com',
                    'secure' => false,
                    'httponly' => false,
                    'samesite' => SameSite::Strict,
                ],
                'lifetime' => 60,
                'storage' => ['dir' => '/tmp/custom'],
                'gc' => ['divisor' => 0],
            ],
        ]);

        $options = SessionOptions::fromConfig($config);

        self::assertSame('app_sid', $options->cookieName);
        self::assertSame('/app', $options->cookiePath);
        self::assertSame('example.com', $options->cookieDomain);
        self::assertFalse($options->cookieSecure);
        self::assertFalse($options->cookieHttpOnly);
        self::assertSame(SameSite::Strict, $options->cookieSameSite);
        self::assertSame(60, $options->lifetime);
        self::assertSame('/tmp/custom', $options->storageDir);
        self::assertSame(0, $options->gcDivisor);
    }

    #[Test]
    public function samesite_accepts_case_insensitive_strings(): void
    {
        $options = SessionOptions::fromConfig(new Config([
            'session' => ['cookie' => ['samesite' => 'strict']],
        ]));

        self::assertSame(SameSite::Strict, $options->cookieSameSite);
    }

    #[Test]
    public function integer_options_accept_numeric_strings(): void
    {
        $options = SessionOptions::fromConfig(new Config([
            'session' => ['lifetime' => '3600', 'gc' => ['divisor' => '50']],
        ]));

        self::assertSame(3600, $options->lifetime);
        self::assertSame(50, $options->gcDivisor);
    }

    #[Test]
    public function unknown_samesite_string_throws(): void
    {
        $this->expectException(InvalidSessionOptionException::class);

        SessionOptions::fromConfig(new Config([
            'session' => ['cookie' => ['samesite' => 'sideways']],
        ]));
    }

    #[Test]
    public function non_boolean_secure_throws(): void
    {
        $this->expectException(InvalidSessionOptionException::class);

        SessionOptions::fromConfig(new Config([
            'session' => ['cookie' => ['secure' => 'yes']],
        ]));
    }

    #[Test]
    public function non_integer_lifetime_throws(): void
    {
        $this->expectException(InvalidSessionOptionException::class);

        SessionOptions::fromConfig(new Config([
            'session' => ['lifetime' => 'soon'],
        ]));
    }

    #[Test]
    public function non_string_cookie_name_throws(): void
    {
        $this->expectException(InvalidSessionOptionException::class);

        SessionOptions::fromConfig(new Config([
            'session' => ['cookie' => ['name' => 123]],
        ]));
    }

    #[Test]
    public function non_string_cookie_domain_throws(): void
    {
        $this->expectException(InvalidSessionOptionException::class);

        SessionOptions::fromConfig(new Config([
            'session' => ['cookie' => ['domain' => 123]],
        ]));
    }

    #[Test]
    public function non_string_samesite_throws(): void
    {
        $this->expectException(InvalidSessionOptionException::class);

        SessionOptions::fromConfig(new Config([
            'session' => ['cookie' => ['samesite' => 5]],
        ]));
    }
}
