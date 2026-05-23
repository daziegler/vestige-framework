<?php

declare(strict_types=1);

namespace App\Http;

use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Vestige\Http\ControllerInterface;

final class HelloController implements ControllerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return new Response(200, ['Content-Type' => 'text/plain'], 'Hello, Vestige!');
    }
}