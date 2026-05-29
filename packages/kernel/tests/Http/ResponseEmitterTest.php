<?php

declare(strict_types=1);

namespace Vestige\Tests\Http;

use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Vestige\Http\ResponseEmitter;

#[CoversClass(ResponseEmitter::class)]
final class ResponseEmitterTest extends TestCase
{
    #[Test]
    public function emits_the_response_body_to_output(): void
    {
        $factory = new Psr17Factory();
        $response = $factory->createResponse(200)
            ->withBody($factory->createStream('Hello, body!'));

        ob_start();
        new ResponseEmitter()->emit($response);
        $output = ob_get_clean();

        self::assertSame('Hello, body!', $output);
    }

    #[Test]
    public function rewinds_a_consumed_body_before_emitting(): void
    {
        $factory = new Psr17Factory();
        $stream = $factory->createStream('chunked content');
        $stream->getContents(); // move pointer to EOF
        $response = $factory->createResponse(200)->withBody($stream);

        ob_start();
        new ResponseEmitter()->emit($response);
        $output = ob_get_clean();

        self::assertSame('chunked content', $output);
    }
}