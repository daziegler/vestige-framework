<?php

declare(strict_types=1);

namespace Vestige\Tests\Http\Error\Format;

use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Vestige\Http\Error\Format\DebugHtmlErrorFormatRenderer;
use Vestige\Http\Error\Format\Responder;
use Vestige\Http\HttpStatus;
use Vestige\Http\Problem\Problem;

#[CoversClass(DebugHtmlErrorFormatRenderer::class)]
final class DebugHtmlErrorFormatRendererTest extends TestCase
{
    #[Test]
    public function advertises_the_html_media_type(): void
    {
        self::assertSame(['text/html'], $this->renderer()->mediaTypes());
    }

    private function renderer(): DebugHtmlErrorFormatRenderer
    {
        $factory = new Psr17Factory();

        return new DebugHtmlErrorFormatRenderer(new Responder($factory, $factory));
    }

    #[Test]
    public function renders_a_verbose_page_with_exception_details(): void
    {
        $response = $this->renderer()->render(
            Problem::forStatus(HttpStatus::InternalServerError),
            new RuntimeException('the real cause'),
        );

        self::assertSame(500, $response->getStatusCode());
        self::assertStringContainsString('text/html', $response->getHeaderLine('Content-Type'));

        $body = (string) $response->getBody();
        self::assertStringContainsString('Internal Server Error', $body);
        self::assertStringContainsString('the real cause', $body);
        self::assertStringContainsString(RuntimeException::class, $body);
    }

    #[Test]
    public function escapes_html_in_the_exception_message(): void
    {
        $response = $this->renderer()->render(
            Problem::forStatus(HttpStatus::InternalServerError),
            new RuntimeException('<script>alert(1)</script>'),
        );

        $body = (string) $response->getBody();
        self::assertStringNotContainsString('<script>alert(1)</script>', $body);
        self::assertStringContainsString('&lt;script&gt;', $body);
    }

    #[Test]
    public function substitutes_invalid_utf8_instead_of_blanking_the_page(): void
    {
        $response = $this->renderer()->render(
            Problem::forStatus(HttpStatus::InternalServerError),
            new RuntimeException("broken \xFF byte"),
        );

        $body = (string) $response->getBody();
        self::assertStringContainsString('broken', $body);
        self::assertStringContainsString("\u{FFFD}", $body);
    }

    #[Test]
    public function renders_problem_detail_instance_and_extensions(): void
    {
        $problem = new Problem(
            status: HttpStatus::UnprocessableEntity,
            title: 'Unprocessable Entity',
            detail: 'The name field is required.',
            instance: '/users',
        )->withExtension('traceId', 'abc-123');

        $response = $this->renderer()->render($problem, new RuntimeException('cause'));

        $body = (string) $response->getBody();
        self::assertStringContainsString('The name field is required.', $body);
        self::assertStringContainsString('/users', $body);
        self::assertStringContainsString('traceId', $body);
        self::assertStringContainsString('abc-123', $body);
    }

    #[Test]
    public function json_encodes_non_string_extension_values(): void
    {
        $problem = Problem::forStatus(HttpStatus::UnprocessableEntity)
            ->withExtension('errors', ['name' => 'required']);

        $response = $this->renderer()->render($problem, new RuntimeException('cause'));

        $body = (string) $response->getBody();
        self::assertStringContainsString('errors', $body);
        self::assertStringContainsString('{&quot;name&quot;:&quot;required&quot;}', $body);
    }
}
