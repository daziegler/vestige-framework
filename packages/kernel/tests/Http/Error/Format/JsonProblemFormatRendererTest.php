<?php

declare(strict_types=1);

namespace Vestige\Tests\Http\Error\Format;

use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Vestige\Http\Error\Format\JsonProblemFormatRenderer;
use Vestige\Http\Error\Format\Responder;
use Vestige\Http\HttpStatus;
use Vestige\Http\Problem\Problem;

#[CoversClass(JsonProblemFormatRenderer::class)]
final class JsonProblemFormatRendererTest extends TestCase
{
    #[Test]
    public function advertises_problem_json_and_plain_json_media_types(): void
    {
        $mediaTypes = $this->renderer()->mediaTypes();

        self::assertContains('application/problem+json', $mediaTypes);
        self::assertContains('application/json', $mediaTypes);
    }

    private function renderer(): JsonProblemFormatRenderer
    {
        $factory = new Psr17Factory();

        return new JsonProblemFormatRenderer(new Responder($factory, $factory));
    }

    #[Test]
    public function renders_problem_as_problem_json(): void
    {
        $response = $this->renderer()->render(
            Problem::forStatus(HttpStatus::NotFound),
            new RuntimeException('internal secret'),
        );

        self::assertSame(404, $response->getStatusCode());
        self::assertSame('application/problem+json', $response->getHeaderLine('Content-Type'));

        $data = json_decode((string) $response->getBody(), true);
        self::assertSame('Not Found', $data['title']);
        self::assertSame(404, $data['status']);
        self::assertSame('about:blank', $data['type']);
    }

    #[Test]
    public function never_leaks_the_throwable_message_or_trace(): void
    {
        $response = $this->renderer()->render(
            Problem::forStatus(HttpStatus::InternalServerError),
            new RuntimeException('internal secret'),
        );

        $body = (string) $response->getBody();
        self::assertStringNotContainsString('internal secret', $body);
        self::assertStringNotContainsString('trace', $body);
    }
}
