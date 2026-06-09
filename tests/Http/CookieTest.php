<?php

declare(strict_types=1);

namespace Vestige\Tests\Http;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Vestige\Http\Cookie;
use Vestige\Http\SameSite;

#[CoversClass(Cookie::class)]
final class CookieTest extends TestCase
{
    #[Test]
    public function renders_all_attributes(): void
    {
        $cookie = new Cookie(
            name: 'vestige_session',
            value: 'abc123',
            maxAge: 7200,
            path: '/',
            domain: 'example.com',
            secure: true,
            httpOnly: true,
            sameSite: SameSite::Lax,
        );

        self::assertSame(
            'vestige_session=abc123; Max-Age=7200; Path=/; Domain=example.com; Secure; HttpOnly; SameSite=Lax',
            (string) $cookie,
        );
    }

    #[Test]
    public function omits_domain_secure_and_httponly_when_unset(): void
    {
        $cookie = new Cookie(
            name: 's',
            value: 'v',
            maxAge: 60,
            path: '/app',
            domain: null,
            secure: false,
            httpOnly: false,
            sameSite: SameSite::Strict,
        );

        self::assertSame('s=v; Max-Age=60; Path=/app; SameSite=Strict', (string) $cookie);
    }

    #[Test]
    public function samesite_none_without_secure_throws(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Cookie(
            name: 's',
            value: 'v',
            maxAge: 60,
            path: '/',
            domain: null,
            secure: false,
            httpOnly: true,
            sameSite: SameSite::None,
        );
    }

    #[Test]
    public function expired_carries_name_path_and_domain(): void
    {
        $cookie = Cookie::expired(
            name: 'vestige_session',
            path: '/app',
            domain: 'example.com',
            secure: true,
            httpOnly: true,
            sameSite: SameSite::Lax,
        );

        self::assertSame(
            'vestige_session=; Max-Age=0; Path=/app; Domain=example.com; Secure; HttpOnly; SameSite=Lax',
            (string) $cookie,
        );
    }
}