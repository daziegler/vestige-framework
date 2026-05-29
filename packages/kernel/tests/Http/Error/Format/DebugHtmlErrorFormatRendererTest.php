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
}
