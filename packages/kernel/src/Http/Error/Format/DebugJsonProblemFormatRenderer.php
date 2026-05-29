<?php

declare(strict_types=1);

namespace Vestige\Http\Error\Format;

use Psr\Http\Message\ResponseInterface;
use Throwable;
use Vestige\Http\Error\FormatRendererInterface;
use Vestige\Http\Problem\Problem;

final readonly class DebugJsonProblemFormatRenderer implements FormatRendererInterface
{
    public function __construct(private JsonProblemFormatRenderer $inner) {}

    /** @return list<string> */
    public function mediaTypes(): array
    {
        return $this->inner->mediaTypes();
    }

    public function render(Problem $problem, Throwable $throwable): ResponseInterface
    {
        return $this->inner->render($this->withDebugMembers($problem, $throwable), $throwable);
    }

    private function withDebugMembers(Problem $problem, Throwable $throwable): Problem
    {
        return $problem
            ->withExtension('exception', $throwable::class)
            ->withExtension('message', $throwable->getMessage())
            ->withExtension('file', $throwable->getFile())
            ->withExtension('line', $throwable->getLine())
            ->withExtension('trace', explode("\n", $throwable->getTraceAsString()));
    }
}