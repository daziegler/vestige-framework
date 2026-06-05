<?php

declare(strict_types=1);

namespace Vestige\Http\Error;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

interface ErrorRendererInterface
{
    public function render(Throwable $throwable, ServerRequestInterface $request): ResponseInterface;
}