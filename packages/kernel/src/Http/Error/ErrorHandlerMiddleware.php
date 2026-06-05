<?php

declare(strict_types=1);

namespace Vestige\Http\Error;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Throwable;
use Vestige\Http\Exceptions\HttpExceptionInterface;

final readonly class ErrorHandlerMiddleware implements MiddlewareInterface
{
    public function __construct(
        private LoggerInterface $logger,
        private ErrorRendererInterface $renderer,
    ) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (Throwable $throwable) {
            $this->log($throwable);

            return $this->renderer->render($throwable, $request);
        }
    }

    private function log(Throwable $throwable): void
    {
        $shouldLog = match (true) {
            $throwable instanceof HttpExceptionInterface => $throwable->getStatusCode()->isServerError(),
            default => true,
        };

        if ($shouldLog === false) {
            return;
        }

        $this->logger->error($throwable->getMessage(), ['exception' => $throwable]);
    }
}