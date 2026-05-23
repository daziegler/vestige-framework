<?php

declare(strict_types=1);

namespace Vestige\Tests\Http\Fixtures;

use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Vestige\Http\ControllerInterface;

final class NameEchoController implements ControllerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $name = $request->getAttribute('name', '');

        return new Response(200, [], is_string($name) ? $name : '');
    }
}
