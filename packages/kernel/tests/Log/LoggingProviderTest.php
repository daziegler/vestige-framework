<?php

declare(strict_types=1);

namespace Vestige\Tests\Log;

use League\Container\Container as LeagueContainer;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Vestige\Container\Container;
use Vestige\Log\LoggingProvider;

#[CoversClass(LoggingProvider::class)]
final class LoggingProviderTest extends TestCase
{
    #[Test]
    public function binds_a_psr3_logger(): void
    {
        $logger = $this->container()->get(LoggerInterface::class);

        self::assertInstanceOf(LoggerInterface::class, $logger);
        self::assertInstanceOf(Logger::class, $logger);
    }

    private function container(): Container
    {
        $container = new Container(new LeagueContainer());
        new LoggingProvider()->register($container);

        return $container;
    }

    #[Test]
    public function logger_writes_to_stderr(): void
    {
        $logger = $this->container()->get(LoggerInterface::class);

        self::assertInstanceOf(Logger::class, $logger);
        $handler = $logger->getHandlers()[0];
        self::assertInstanceOf(StreamHandler::class, $handler);
        self::assertSame('php://stderr', $handler->getUrl());
    }

    #[Test]
    public function logger_binding_is_shared(): void
    {
        $container = $this->container();

        self::assertSame(
            $container->get(LoggerInterface::class),
            $container->get(LoggerInterface::class),
        );
    }
}
