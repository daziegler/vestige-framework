<?php

declare(strict_types=1);

use Psr\Log\LoggerInterface;
use Vestige\Http\Error\ErrorHandlerMiddleware;
use Vestige\Http\Error\ProdErrorProvider;
use Vestige\Log\LoggingProvider;

return [
    'providers' => [
        LoggingProvider::class,
        ProdErrorProvider::class,
    ],
    'eager' => [
        LoggerInterface::class,
    ],
    'middleware' => [
        ErrorHandlerMiddleware::class,
    ],
];