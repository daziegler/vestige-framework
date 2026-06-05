<?php

declare(strict_types=1);

namespace Vestige\Http\Error\Format;

use Psr\Http\Message\ResponseInterface;
use Throwable;
use Vestige\Http\Error\FormatRendererInterface;
use Vestige\Http\Problem\Problem;
use Vestige\Http\Problem\Rfc9457;

final readonly class JsonProblemFormatRenderer implements FormatRendererInterface
{
    public function __construct(private Responder $responder) {}

    /** @return list<string> */
    public function mediaTypes(): array
    {
        return [Rfc9457::MEDIA_TYPE_JSON, 'application/json'];
    }

    public function render(Problem $problem, Throwable $throwable): ResponseInterface
    {
        $body = json_encode(
            $problem->toArray(),
            JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE,
        );

        return $this->responder->respond($problem->getStatus()->value, Rfc9457::MEDIA_TYPE_JSON, $body);
    }
}