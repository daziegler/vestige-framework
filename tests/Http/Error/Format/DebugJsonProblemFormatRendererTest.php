<?php

declare(strict_types=1);

namespace Vestige\Tests\Http\Error\Format;

use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Vestige\Http\Error\Format\DebugJsonProblemFormatRenderer;
use Vestige\Http\Error\Format\JsonProblemFormatRenderer;
use Vestige\Http\Error\Format\Responder;
use Vestige\Http\HttpStatus;
use Vestige\Http\Problem\Problem;

#[CoversClass(DebugJsonProblemFormatRenderer::class)]
final class DebugJsonProblemFormatRendererTest extends TestCase
{
    #[Test]
    public function advertises_the_same_media_types_as_the_plain_renderer(): void
    {
        $mediaTypes = $this->renderer()->mediaTypes();

        self::assertContains('application/problem+json', $mediaTypes);
        self::assertContains('application/json', $mediaTypes);
    }

    private function renderer(): DebugJsonProblemFormatRenderer
    {
        $factory = new Psr17Factory();

        return new DebugJsonProblemFormatRenderer(
            new JsonProblemFormatRenderer(new Responder($factory, $factory)),
        );
    }

    #[Test]
    public function includes_debug_members_from_the_throwable(): void
    {
        $response = $this->renderer()->render(
            Problem::forStatus(HttpStatus::InternalServerError),
            new RuntimeException('the real cause'),
        );

        self::assertSame('application/problem+json', $response->getHeaderLine('Content-Type'));

        $data = json_decode((string) $response->getBody(), true);
        self::assertIsArray($data);
        self::assertSame(500, $data['status']);
        self::assertSame(RuntimeException::class, $data['exception']);
        self::assertSame('the real cause', $data['message']);
        self::assertArrayHasKey('file', $data);
        self::assertArrayHasKey('line', $data);
        self::assertIsArray($data['trace']);
    }

    #[Test]
    public function renders_a_throwable_with_invalid_utf8_in_the_message(): void
    {
        $response = $this->renderer()->render(
            Problem::forStatus(HttpStatus::InternalServerError),
            new RuntimeException("broken \xFF byte"),
        );

        $data = json_decode((string) $response->getBody(), true);
        self::assertIsArray($data);
        self::assertSame("broken \u{FFFD} byte", $data['message']);
    }
}
