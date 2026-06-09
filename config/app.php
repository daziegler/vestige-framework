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

return [
    'providers' => [
        LoggingProvider::class,
        SessionProvider::class,
        match (Environment::fromGlobals()) {
            Environment::Development => DevErrorProvider::class,
            default => ProdErrorProvider::class,
        },
    ],
    'eager' => [
        LoggerInterface::class,
    ],
    'middleware' => [
        SessionMiddleware::class,
        ErrorHandlerMiddleware::class,
    ],
];
