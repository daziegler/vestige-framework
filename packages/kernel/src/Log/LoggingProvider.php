<?php

declare(strict_types=1);

namespace Vestige\Log;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Vestige\Container\ContainerInterface;
use Vestige\Container\ServiceProviderInterface;

final class LoggingProvider implements ServiceProviderInterface
{
    private const string CHANNEL = 'vestige';
    private const string STDERR = 'php://stderr';

    public function register(ContainerInterface $container): void
    {
        $container->bind(LoggerInterface::class, static function (): LoggerInterface {
            $logger = new Logger(self::CHANNEL);
            $logger->pushHandler(new StreamHandler(self::STDERR));

            return $logger;
        })->shared();
    }
}