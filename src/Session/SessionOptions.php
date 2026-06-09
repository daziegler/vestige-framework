<?php

declare(strict_types=1);

namespace Vestige\Session;

use Vestige\Config\Config;
use Vestige\Http\SameSite;
use Vestige\Session\Exceptions\InvalidSessionOptionException;

final readonly class SessionOptions
{
    public function __construct(
        public string $cookieName,
        public string $cookiePath,
        public ?string $cookieDomain,
        public bool $cookieSecure,
        public bool $cookieHttpOnly,
        public SameSite $cookieSameSite,
        public int $lifetime,
        public string $storageDir,
        public int $gcDivisor,
    ) {}

    public static function fromConfig(Config $config): self
    {
        return new self(
            cookieName: self::stringOption($config, 'session.cookie.name', 'vestige_session'),
            cookiePath: self::stringOption($config, 'session.cookie.path', '/'),
            cookieDomain: self::nullableStringOption($config, 'session.cookie.domain'),
            cookieSecure: self::boolOption($config, 'session.cookie.secure', true),
            cookieHttpOnly: self::boolOption($config, 'session.cookie.httponly', true),
            cookieSameSite: self::sameSiteOption($config, 'session.cookie.samesite'),
            lifetime: self::intOption($config, 'session.lifetime', 7200),
            storageDir: self::nullableStringOption($config, 'session.storage.dir') ?? sys_get_temp_dir() . '/vestige_sessions',
            gcDivisor: self::intOption($config, 'session.gc.divisor', 100),
        );
    }

    private static function stringOption(Config $config, string $key, string $default): string
    {
        $value = $config->getOr($key, $default);
        if (is_string($value) === false) {
            throw InvalidSessionOptionException::forKey($key, 'string', $value);
        }

        return $value;
    }

    private static function nullableStringOption(Config $config, string $key): ?string
    {
        $value = $config->getOr($key, null);
        if ($value !== null && is_string($value) === false) {
            throw InvalidSessionOptionException::forKey($key, 'string|null', $value);
        }

        return $value;
    }

    private static function boolOption(Config $config, string $key, bool $default): bool
    {
        $value = $config->getOr($key, $default);
        if (is_bool($value) === false) {
            throw InvalidSessionOptionException::forKey($key, 'bool', $value);
        }

        return $value;
    }

    private static function intOption(Config $config, string $key, int $default): int
    {
        $value = $config->getOr($key, $default);
        if (is_string($value) && ctype_digit($value)) {
            return (int) $value;
        }

        if (is_int($value) === false) {
            throw InvalidSessionOptionException::forKey($key, 'int', $value);
        }

        return $value;
    }

    private static function sameSiteOption(Config $config, string $key): SameSite
    {
        $value = $config->getOr($key, SameSite::Lax);
        if ($value instanceof SameSite) {
            return $value;
        }

        if (is_string($value) === false) {
            throw InvalidSessionOptionException::forKey($key, 'SameSite|string', $value);
        }

        $parsed = SameSite::tryFrom(ucfirst(strtolower($value)));
        if ($parsed === null) {
            throw InvalidSessionOptionException::forKey($key, 'SameSite|string', $value);
        }

        return $parsed;
    }
}
