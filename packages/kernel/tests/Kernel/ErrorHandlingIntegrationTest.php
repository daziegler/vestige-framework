<?php

declare(strict_types=1);

namespace Vestige\Tests\Kernel;

use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Vestige\Http\Error\ErrorHandlerMiddleware;
use Vestige\Http\Error\ErrorRenderer;
use Vestige\Http\Error\ProdErrorProvider;
use Vestige\Kernel;
use Vestige\Log\LoggingProvider;

#[CoversClass(ErrorHandlerMiddleware::class)]
#[CoversClass(ErrorRenderer::class)]
#[CoversClass(ProdErrorProvider::class)]
#[CoversClass(LoggingProvider::class)]
final class ErrorHandlingIntegrationTest extends TestCase
{
    #[Test]
    public function unmatched_route_renders_problem_json_when_json_is_accepted(): void
    {
        $request = new Psr17Factory()->createServerRequest('GET', '/missing')
            ->withHeader('Accept', 'application/json');

        $response = $this->bootedKernel()->handle($request);

        self::assertSame(404, $response->getStatusCode());
        self::assertSame('application/problem+json', $response->getHeaderLine('Content-Type'));

        $data = json_decode((string) $response->getBody(), true);
        self::assertIsArray($data);
        self::assertSame(404, $data['status']);
        self::assertSame('Not Found', $data['title']);
        self::assertSame('/missing', $data['instance']);
    }

    private function bootedKernel(): Kernel
    {
        $kernel = new Kernel(__DIR__ . '/apps/error-app');
        $kernel->boot();

        return $kernel;
    }

    #[Test]
    public function unmatched_route_renders_html_when_html_is_accepted(): void
    {
        $request = new Psr17Factory()->createServerRequest('GET', '/missing')
            ->withHeader('Accept', 'text/html');

        $response = $this->bootedKernel()->handle($request);

        self::assertSame(404, $response->getStatusCode());
        self::assertStringContainsString('text/html', $response->getHeaderLine('Content-Type'));
        self::assertStringContainsString('Not Found', (string) $response->getBody());
    }

    #[Test]
    public function matched_route_is_unaffected_by_the_error_middleware(): void
    {
        $request = new Psr17Factory()->createServerRequest('GET', '/');

        $response = $this->bootedKernel()->handle($request);

        self::assertSame(200, $response->getStatusCode());
    }
}