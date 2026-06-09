<?php

declare(strict_types=1);

namespace Vestige\Tests\Session;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Vestige\Config\Config;
use Vestige\Http\SameSite;
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
}