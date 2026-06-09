<?php

declare(strict_types=1);

namespace Vestige\Tests\Kernel\Fixtures;

use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Vestige\Http\ControllerInterface;

final readonly class GreetingController implements ControllerInterface
{
    public function __construct(private Greeting $greeting) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return new Response(200, [], $this->greeting->message);
    }
}
