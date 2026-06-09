<?php

declare(strict_types=1);

namespace Vestige\Session;

use Psr\Clock\ClockInterface;
use Vestige\Clock\SystemClock;
use Vestige\Config\Config;
use Vestige\Container\ContainerInterface;
use Vestige\Container\ServiceProviderInterface;
use Vestige\Session\Storage\FileSessionStorage;
use Vestige\Session\Storage\SessionStorageInterface;

final class SessionProvider implements ServiceProviderInterface
{
    public function register(ContainerInterface $container): void
    {
        $container->bind(SessionOptions::class, static function () use ($container): SessionOptions {
            return SessionOptions::fromConfig($container->get(Config::class));
        })->shared();

        $container->bind(SessionContext::class, static function (): SessionContext {
            return new SessionContext();
        })->shared();

        $container->bind(SessionInterface::class, static function () use ($container): SessionInterface {
            return new SessionProxy($container->get(SessionContext::class));
        })->shared();

        if ($container->has(ClockInterface::class) === false) {
            $container->bind(ClockInterface::class, static function (): ClockInterface {
                return new SystemClock();
            })->shared();
        }

        $container->bind(SessionStorageInterface::class, static function () use ($container): SessionStorageInterface {
            $options = $container->get(SessionOptions::class);

            return new FileSessionStorage($options->storageDir, $container->get(ClockInterface::class));
        })->shared();

        $container->bind(SessionMiddleware::class, static function () use ($container): SessionMiddleware {
            return new SessionMiddleware(
                $container->get(SessionStorageInterface::class),
                $container->get(SessionContext::class),
                $container->get(SessionOptions::class),
            );
        })->shared();
    }
}
