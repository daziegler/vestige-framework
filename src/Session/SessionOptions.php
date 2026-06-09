<?php

declare(strict_types=1);

namespace Vestige\Session;

use Vestige\Config\Config;
use Vestige\Http\SameSite;

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
        /** @var string $cookieName */
        $cookieName = $config->getOr('session.cookie.name', 'vestige_session');
        /** @var string $cookiePath */
        $cookiePath = $config->getOr('session.cookie.path', '/');
        /** @var string|null $cookieDomain */
        $cookieDomain = $config->getOr('session.cookie.domain', null);
        /** @var bool $cookieSecure */
        $cookieSecure = $config->getOr('session.cookie.secure', true);
        /** @var bool $cookieHttpOnly */
        $cookieHttpOnly = $config->getOr('session.cookie.httponly', true);
        /** @var SameSite $cookieSameSite */
        $cookieSameSite = $config->getOr('session.cookie.samesite', SameSite::Lax);
        /** @var int $lifetime */
        $lifetime = $config->getOr('session.lifetime', 7200);
        /** @var string|null $storageDir */
        $storageDir = $config->getOr('session.storage.dir', null);
        /** @var int $gcDivisor */
        $gcDivisor = $config->getOr('session.gc.divisor', 100);

        return new self(
            cookieName: $cookieName,
            cookiePath: $cookiePath,
            cookieDomain: $cookieDomain,
            cookieSecure: $cookieSecure,
            cookieHttpOnly: $cookieHttpOnly,
            cookieSameSite: $cookieSameSite,
            lifetime: $lifetime,
            storageDir: $storageDir ?? sys_get_temp_dir() . '/vestige_sessions',
            gcDivisor: $gcDivisor,
        );
    }
}