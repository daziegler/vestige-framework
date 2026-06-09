<?php

declare(strict_types=1);

namespace Vestige\Tests\Http;

use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Vestige\Http\Exceptions\HeadersAlreadySentException;
use Vestige\Http\ResponseEmitter;
use Vestige\Tests\Http\Fixtures\HeaderSpy;

#[CoversClass(ResponseEmitter::class)]
#[CoversClass(HeadersAlreadySentException::class)]
final class ResponseEmitterTest extends TestCase
{
    protected function setUp(): void
    {
        HeaderSpy::reset();
    }

    #[Test]
    public function emits_the_response_body_to_output(): void
    {
        $factory = new Psr17Factory();
        $response = $factory->createResponse(200)
            ->withBody($factory->createStream('Hello, body!'));

        ob_start();
        try {
            new ResponseEmitter()->emit($response);
        } finally {
            $output = ob_get_clean();
        }

        self::assertSame('Hello, body!', $output);
    }

    #[Test]
    public function rewinds_a_consumed_body_before_emitting(): void
    {
        $factory = new Psr17Factory();
        $stream = $factory->createStream('chunked content');
        // move pointer to EOF
        $stream->getContents();

        $response = $factory->createResponse(200)->withBody($stream);

        ob_start();
        try {
            new ResponseEmitter()->emit($response);
        } finally {
            $output = ob_get_clean();
        }

        self::assertSame('chunked content', $output);
    }

    #[Test]
    public function emits_headers_followed_by_the_status_line(): void
    {
        $factory = new Psr17Factory();
        $response = $factory->createResponse(404)
            ->withHeader('Content-Type', 'text/plain');

        $this->emit($response);

        [$contentType, $statusLine] = HeaderSpy::$headers;
        self::assertSame('Content-Type: text/plain', $contentType['header']);
        self::assertTrue($contentType['replace']);
        self::assertSame('HTTP/1.1 404 Not Found', $statusLine['header']);
        self::assertSame(404, $statusLine['statusCode']);
    }

    #[Test]
    public function replaces_only_the_first_value_of_a_multi_value_header(): void
    {
        $factory = new Psr17Factory();
        $response = $factory->createResponse(200)
            ->withAddedHeader('Cache-Control', 'no-cache')
            ->withAddedHeader('Cache-Control', 'no-store');

        $this->emit($response);

        [$first, $second] = HeaderSpy::$headers;
        self::assertSame('Cache-Control: no-cache', $first['header']);
        self::assertTrue($first['replace']);
        self::assertSame('Cache-Control: no-store', $second['header']);
        self::assertFalse($second['replace']);
    }

    #[Test]
    public function never_replaces_set_cookie_headers(): void
    {
        $factory = new Psr17Factory();
        $response = $factory->createResponse(200)
            ->withAddedHeader('Set-Cookie', 'a=1')
            ->withAddedHeader('Set-Cookie', 'b=2');

        $this->emit($response);

        [$first, $second] = HeaderSpy::$headers;
        self::assertSame('Set-Cookie: a=1', $first['header']);
        self::assertFalse($first['replace']);
        self::assertSame('Set-Cookie: b=2', $second['header']);
        self::assertFalse($second['replace']);
    }

    #[Test]
    public function throws_when_headers_are_already_sent(): void
    {
        HeaderSpy::$headersSent = true;
        $response = new Psr17Factory()->createResponse(200);

        $this->expectException(HeadersAlreadySentException::class);
        new ResponseEmitter()->emit($response);
    }

    private function emit(ResponseInterface $response): void
    {
        ob_start();
        try {
            new ResponseEmitter()->emit($response);
        } finally {
            ob_end_clean();
        }
    }
}
