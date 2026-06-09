<?php

declare(strict_types=1);

namespace Vestige\Tests\Session;

use DateTimeImmutable;
use League\Container\Container as LeagueContainer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Clock\ClockInterface;
use Vestige\Clock\SystemClock;
use Vestige\Config\Config;
use Vestige\Container\Container;
use Vestige\Session\SessionContext;
use Vestige\Session\SessionInterface;
use Vestige\Session\SessionMiddleware;
use Vestige\Session\SessionOptions;
use Vestige\Session\SessionProvider;
use Vestige\Session\SessionProxy;
use Vestige\Session\Storage\FileSessionStorage;
use Vestige\Session\Storage\SessionStorageInterface;
use Vestige\Tests\Clock\Fixtures\FrozenClock;

#[CoversClass(SessionProvider::class)]
final class SessionProviderTest extends TestCase
{
    private function container(): Container
    {
        $container = new Container(new LeagueContainer());
        $container->bind(Config::class, new Config([]));

        return $container;
    }

    #[Test]
    public function registers_all_session_services(): void
    {
        $container = $this->container();

        new SessionProvider()->register($container);

        self::assertInstanceOf(SessionProxy::class, $container->get(SessionInterface::class));
        self::assertInstanceOf(FileSessionStorage::class, $container->get(SessionStorageInterface::class));
        self::assertInstanceOf(SystemClock::class, $container->get(ClockInterface::class));
        self::assertInstanceOf(SessionOptions::class, $container->get(SessionOptions::class));
        self::assertInstanceOf(SessionMiddleware::class, $container->get(SessionMiddleware::class));
    }

    #[Test]
    public function context_is_shared(): void
    {
        $container = $this->container();

        new SessionProvider()->register($container);

        self::assertSame($container->get(SessionContext::class), $container->get(SessionContext::class));
    }

    #[Test]
    public function existing_clock_binding_wins(): void
    {
        $container = $this->container();
        $clock = new FrozenClock(new DateTimeImmutable('2026-06-09 12:00:00'));
        $container->bind(ClockInterface::class, $clock);

        new SessionProvider()->register($container);

        self::assertSame($clock, $container->get(ClockInterface::class));
    }
}