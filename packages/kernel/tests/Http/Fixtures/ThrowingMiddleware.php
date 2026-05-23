<?php

declare(strict_types=1);

namespace Vestige\Tests\Http\Fixtures;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RuntimeException;

final class ThrowingMiddleware implements MiddlewareInterface
{
    public function __construct()
    {
        throw new RuntimeException('Should never be constructed.');
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        throw new RuntimeException('unreachable');
    }
}
