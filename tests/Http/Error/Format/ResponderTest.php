<?php

declare(strict_types=1);

namespace Vestige\Tests\Http\Error\Format;

use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Vestige\Http\Error\Format\Responder;

#[CoversClass(Responder::class)]
final class ResponderTest extends TestCase
{
    #[Test]
    public function builds_a_response_with_status_content_type_and_body(): void
    {
        $factory = new Psr17Factory();
        $responder = new Responder($factory, $factory);

        $response = $responder->respond(404, 'application/problem+json', '{"status":404}');

        self::assertSame(404, $response->getStatusCode());
        self::assertSame('application/problem+json', $response->getHeaderLine('Content-Type'));
        self::assertSame('{"status":404}', (string) $response->getBody());
    }
}
