<?php

declare(strict_types=1);

namespace Vestige\Tests\Http\Error;

use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;
use Vestige\Http\Error\ErrorRenderer;
use Vestige\Http\Error\Format\HtmlErrorFormatRenderer;
use Vestige\Http\Error\Format\JsonProblemFormatRenderer;
use Vestige\Http\Error\Format\Responder;
use Vestige\Http\Exceptions\MethodNotAllowedException;
use Vestige\Http\Exceptions\NotFoundException;
use Vestige\Http\HttpMethod;

#[CoversClass(ErrorRenderer::class)]
final class ErrorRendererTest extends TestCase
{
    #[Test]
    public function negotiates_json_when_json_is_accepted(): void
    {
        $response = $this->renderer()->render(new NotFoundException(), $this->request('/x', 'application/json'));

        self::assertSame(404, $response->getStatusCode());
        self::assertSame('application/problem+json', $response->getHeaderLine('Content-Type'));
    }

    private function renderer(): ErrorRenderer
    {
        $factory = new Psr17Factory();
        $responder = new Responder($factory, $factory);

        return new ErrorRenderer(
            [new JsonProblemFormatRenderer($responder)],
            new HtmlErrorFormatRenderer($responder),
        );
    }

    private function request(string $path, ?string $accept = null): ServerRequestInterface
    {
        $request = new Psr17Factory()->createServerRequest('GET', $path);

        return $accept === null ? $request : $request->withHeader('Accept', $accept);
    }

    #[Test]
    public function falls_back_to_html_for_browser_accept_headers(): void
    {
        $accept = 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8';

        $response = $this->renderer()->render(new NotFoundException(), $this->request('/x', $accept));

        self::assertStringContainsString('text/html', $response->getHeaderLine('Content-Type'));
    }

    #[Test]
    public function falls_back_to_html_when_no_accept_header_is_present(): void
    {
        $response = $this->renderer()->render(new NotFoundException(), $this->request('/x'));

        self::assertStringContainsString('text/html', $response->getHeaderLine('Content-Type'));
    }

    #[Test]
    public function maps_a_generic_throwable_to_a_500_problem(): void
    {
        $response = $this->renderer()->render(new RuntimeException('boom'), $this->request('/x', 'application/json'));

        self::assertSame(500, $response->getStatusCode());

        $data = json_decode((string) $response->getBody(), true);
        self::assertIsArray($data);
        self::assertSame(500, $data['status']);
        self::assertSame('/x', $data['instance']);
        self::assertArrayNotHasKey('message', $data);
    }

    #[Test]
    public function sets_the_instance_to_the_request_path_for_http_exceptions(): void
    {
        $response = $this->renderer()->render(new NotFoundException(), $this->request('/missing', 'application/json'));

        $data = json_decode((string) $response->getBody(), true);
        self::assertIsArray($data);
        self::assertSame('/missing', $data['instance']);
    }

    #[Test]
    public function propagates_http_exception_headers_onto_the_response(): void
    {
        $exception = new MethodNotAllowedException([HttpMethod::Get, HttpMethod::Post]);

        $response = $this->renderer()->render($exception, $this->request('/x', 'application/json'));

        self::assertSame(405, $response->getStatusCode());
        self::assertSame('GET, POST', $response->getHeaderLine('Allow'));
    }
}
