<?php

declare(strict_types=1);

namespace Vestige\Tests\Http\Error;

use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;
use RuntimeException;
use Vestige\Http\Error\ErrorHandlerMiddleware;
use Vestige\Http\Error\ErrorRenderer;
use Vestige\Http\Error\Format\HtmlErrorFormatRenderer;
use Vestige\Http\Error\Format\JsonProblemFormatRenderer;
use Vestige\Http\Error\Format\Responder;
use Vestige\Http\Exceptions\HttpException;
use Vestige\Http\Exceptions\NotFoundException;
use Vestige\Http\HttpStatus;
use Vestige\Tests\Http\Error\Fixtures\SpyLogger;
use Vestige\Tests\Http\Error\Fixtures\ThrowingHandler;
use Vestige\Tests\Http\Fixtures\HelloController;

#[CoversClass(ErrorHandlerMiddleware::class)]
final class ErrorHandlerMiddlewareTest extends TestCase
{
    #[Test]
    public function passes_a_successful_response_through_untouched(): void
    {
        $logger = new SpyLogger();
        $request = new Psr17Factory()->createServerRequest('GET', '/');

        $response = $this->middleware($logger)->process($request, new HelloController());

        self::assertSame(200, $response->getStatusCode());
        self::assertSame([], $logger->records);
    }

    private function middleware(SpyLogger $logger): ErrorHandlerMiddleware
    {
        $factory = new Psr17Factory();
        $responder = new Responder($factory, $factory);
        $renderer = new ErrorRenderer(
            [new JsonProblemFormatRenderer($responder)],
            new HtmlErrorFormatRenderer($responder),
        );

        return new ErrorHandlerMiddleware($logger, $renderer);
    }

    #[Test]
    public function renders_and_logs_a_generic_throwable_as_a_500(): void
    {
        $logger = new SpyLogger();
        $request = new Psr17Factory()->createServerRequest('GET', '/x')->withHeader('Accept', 'application/json');

        $response = $this->middleware($logger)->process($request, new ThrowingHandler(new RuntimeException('boom')));

        self::assertSame(500, $response->getStatusCode());
        self::assertCount(1, $logger->records);
        self::assertSame(LogLevel::ERROR, $logger->records[0]['level']);
        self::assertInstanceOf(RuntimeException::class, $logger->records[0]['context']['exception']);
    }

    #[Test]
    public function does_not_log_client_errors(): void
    {
        $logger = new SpyLogger();
        $request = new Psr17Factory()->createServerRequest('GET', '/x')->withHeader('Accept', 'application/json');

        $response = $this->middleware($logger)->process($request, new ThrowingHandler(new NotFoundException()));

        self::assertSame(404, $response->getStatusCode());
        self::assertSame([], $logger->records);
    }

    #[Test]
    public function logs_server_side_http_exceptions(): void
    {
        $logger = new SpyLogger();
        $request = new Psr17Factory()->createServerRequest('GET', '/x')->withHeader('Accept', 'application/json');

        $serverError = new class extends HttpException {
            public function __construct()
            {
                parent::__construct(HttpStatus::ServiceUnavailable);
            }
        };

        $response = $this->middleware($logger)->process($request, new ThrowingHandler($serverError));

        self::assertSame(503, $response->getStatusCode());
        self::assertCount(1, $logger->records);
        self::assertSame(LogLevel::ERROR, $logger->records[0]['level']);
    }
}