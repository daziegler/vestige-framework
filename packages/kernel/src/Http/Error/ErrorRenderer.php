<?php

declare(strict_types=1);

namespace Vestige\Http\Error;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;
use Vestige\Http\Exceptions\HttpExceptionInterface;
use Vestige\Http\HttpStatus;
use Vestige\Http\Problem\Problem;

final readonly class ErrorRenderer implements ErrorRendererInterface
{
    /** @param list<FormatRendererInterface> $renderers */
    public function __construct(
        private array $renderers,
        private FormatRendererInterface $default,
    ) {}

    public function render(Throwable $throwable, ServerRequestInterface $request): ResponseInterface
    {
        $path = $request->getUri()->getPath();

        if ($throwable instanceof HttpExceptionInterface) {
            $problem = Problem::fromHttpException($throwable, $request);
        } else {
            $problem = new Problem(
                status  : HttpStatus::InternalServerError,
                title   : HttpStatus::InternalServerError->reasonPhrase(),
                instance: $path === '' ? null : $path,
            );
        }

        $response = $this->negotiate($request->getHeaderLine('Accept'))->render($problem, $throwable);

        if ($throwable instanceof HttpExceptionInterface) {
            foreach ($throwable->getHeaders() as $name => $value) {
                $response = $response->withHeader($name, $value);
            }
        }

        return $response;
    }

    private function negotiate(string $accept): FormatRendererInterface
    {
        foreach ($this->parseAccept($accept) as $mediaType) {
            if ($mediaType === '*/*') {
                return $this->default;
            }

            foreach ($this->renderers as $renderer) {
                if (in_array($mediaType, $renderer->mediaTypes(), true)) {
                    return $renderer;
                }
            }
        }

        return $this->default;
    }

    /** @return list<string> */
    private function parseAccept(string $accept): array
    {
        $types = [];

        foreach (explode(',', $accept) as $part) {
            $type = strtolower(trim(explode(';', $part)[0]));
            if ($type !== '') {
                $types[] = $type;
            }
        }

        return $types;
    }
}
