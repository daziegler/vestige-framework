<?php

declare(strict_types=1);

namespace Vestige\Tests\Http\Error\Format;

use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Vestige\Http\Error\Format\HtmlErrorFormatRenderer;
use Vestige\Http\Error\Format\Responder;
use Vestige\Http\HttpStatus;
use Vestige\Http\Problem\Problem;

#[CoversClass(HtmlErrorFormatRenderer::class)]
final class HtmlErrorFormatRendererTest extends TestCase
{
    #[Test]
    public function advertises_the_html_media_type(): void
    {
        self::assertSame(['text/html'], $this->renderer()->mediaTypes());
    }

    private function renderer(): HtmlErrorFormatRenderer
    {
        $factory = new Psr17Factory();

        return new HtmlErrorFormatRenderer(new Responder($factory, $factory));
    }

    #[Test]
    public function renders_a_minimal_html_page_with_status_and_title(): void
    {
        $response = $this->renderer()->render(
            Problem::forStatus(HttpStatus::NotFound),
            new RuntimeException('internal secret'),
        );

        self::assertSame(404, $response->getStatusCode());
        self::assertStringContainsString('text/html', $response->getHeaderLine('Content-Type'));

        $body = (string) $response->getBody();
        self::assertStringContainsString('404', $body);
        self::assertStringContainsString('Not Found', $body);
    }

    #[Test]
    public function never_leaks_the_throwable_message(): void
    {
        $response = $this->renderer()->render(
            Problem::forStatus(HttpStatus::InternalServerError),
            new RuntimeException('internal secret'),
        );

        self::assertStringNotContainsString('internal secret', (string) $response->getBody());
    }

    #[Test]
    public function substitutes_invalid_utf8_in_the_title(): void
    {
        $response = $this->renderer()->render(
            new Problem(status: HttpStatus::InternalServerError, title: "bad \xFF title"),
            new RuntimeException('ignored'),
        );

        self::assertStringContainsString("\u{FFFD}", (string) $response->getBody());
    }
}
