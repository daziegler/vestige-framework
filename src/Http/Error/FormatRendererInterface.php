<?php

declare(strict_types=1);

namespace Vestige\Http\Error;

use Psr\Http\Message\ResponseInterface;
use Throwable;
use Vestige\Http\Problem\Problem;

interface FormatRendererInterface
{
    /** @return list<string> */
    public function mediaTypes(): array;

    public function render(Problem $problem, Throwable $throwable): ResponseInterface;
}
