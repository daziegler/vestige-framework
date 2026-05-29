<?php

declare(strict_types=1);

namespace Vestige\Tests\Kernel\Fixtures;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Vestige\Http\ControllerInterface;

final readonly class FactoryProbeController implements ControllerInterface
{
    public function __construct(private ResponseFactoryInterface $responseFactory) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->responseFactory->createResponse(204);
    }
}