<?php

declare(strict_types=1);

namespace Vestige\Tests\Http\Error\Fixtures;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;

final readonly class ThrowingHandler implements RequestHandlerInterface
{
    public function __construct(private Throwable $throwable) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        throw $this->throwable;
    }
}
