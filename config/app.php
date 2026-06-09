<?php

declare(strict_types=1);

use Psr\Log\LoggerInterface;
use Vestige\Environment;
use Vestige\Http\Error\DevErrorProvider;
use Vestige\Http\Error\ErrorHandlerMiddleware;
use Vestige\Http\Error\ProdErrorProvider;
use Vestige\Log\LoggingProvider;
use Vestige\Session\SessionMiddleware;
use Vestige\Session\SessionProvider;

$env = Environment::tryFrom($_ENV['APP_ENV'] ?? '') ?? Environment::Production;

return [
    'providers' => [
        LoggingProvider::class,
        SessionProvider::class,
        match ($env) {
            Environment::Development => DevErrorProvider::class,
            default => ProdErrorProvider::class,
        },
    ],
    'eager' => [
        LoggerInterface::class,
    ],
    'middleware' => [
        ErrorHandlerMiddleware::class,
        SessionMiddleware::class,
    ],
];
