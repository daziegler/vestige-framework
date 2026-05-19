<?php

declare(strict_types=1);

namespace Vestige\Tests\Http;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Vestige\Http\HttpStatus;

#[CoversClass(HttpStatus::class)]
final class HttpStatusTest extends TestCase
{
    /** @return iterable<string, array{HttpStatus, bool}> */
    public static function clientErrorCases(): iterable
    {
        yield 'BadRequest' => [HttpStatus::BadRequest, true];
        yield 'Unauthorized' => [HttpStatus::Unauthorized, true];
        yield 'Forbidden' => [HttpStatus::Forbidden, true];
        yield 'NotFound' => [HttpStatus::NotFound, true];
        yield 'MethodNotAllowed' => [HttpStatus::MethodNotAllowed, true];
        yield 'Conflict' => [HttpStatus::Conflict, true];
        yield 'UnprocessableEntity' => [HttpStatus::UnprocessableEntity, true];
        yield 'TooManyRequests' => [HttpStatus::TooManyRequests, true];
        yield 'InternalServerError' => [HttpStatus::InternalServerError, false];
        yield 'ServiceUnavailable' => [HttpStatus::ServiceUnavailable, false];
    }

    /** @return iterable<string, array{HttpStatus, bool}> */
    public static function serverErrorCases(): iterable
    {
        yield 'BadRequest' => [HttpStatus::BadRequest, false];
        yield 'Unauthorized' => [HttpStatus::Unauthorized, false];
        yield 'Forbidden' => [HttpStatus::Forbidden, false];
        yield 'NotFound' => [HttpStatus::NotFound, false];
        yield 'MethodNotAllowed' => [HttpStatus::MethodNotAllowed, false];
        yield 'Conflict' => [HttpStatus::Conflict, false];
        yield 'UnprocessableEntity' => [HttpStatus::UnprocessableEntity, false];
        yield 'TooManyRequests' => [HttpStatus::TooManyRequests, false];
        yield 'InternalServerError' => [HttpStatus::InternalServerError, true];
        yield 'ServiceUnavailable' => [HttpStatus::ServiceUnavailable, true];
    }

    #[Test]
    public function cases_have_expected_int_values(): void
    {
        self::assertSame(400, HttpStatus::BadRequest->value);
        self::assertSame(401, HttpStatus::Unauthorized->value);
        self::assertSame(403, HttpStatus::Forbidden->value);
        self::assertSame(404, HttpStatus::NotFound->value);
        self::assertSame(405, HttpStatus::MethodNotAllowed->value);
        self::assertSame(409, HttpStatus::Conflict->value);
        self::assertSame(422, HttpStatus::UnprocessableEntity->value);
        self::assertSame(429, HttpStatus::TooManyRequests->value);
        self::assertSame(500, HttpStatus::InternalServerError->value);
        self::assertSame(503, HttpStatus::ServiceUnavailable->value);
    }

    #[Test]
    public function reason_phrase_returns_canonical_phrase(): void
    {
        self::assertSame('Bad Request', HttpStatus::BadRequest->reasonPhrase());
        self::assertSame('Unauthorized', HttpStatus::Unauthorized->reasonPhrase());
        self::assertSame('Forbidden', HttpStatus::Forbidden->reasonPhrase());
        self::assertSame('Not Found', HttpStatus::NotFound->reasonPhrase());
        self::assertSame('Method Not Allowed', HttpStatus::MethodNotAllowed->reasonPhrase());
        self::assertSame('Conflict', HttpStatus::Conflict->reasonPhrase());
        self::assertSame('Unprocessable Entity', HttpStatus::UnprocessableEntity->reasonPhrase());
        self::assertSame('Too Many Requests', HttpStatus::TooManyRequests->reasonPhrase());
        self::assertSame('Internal Server Error', HttpStatus::InternalServerError->reasonPhrase());
        self::assertSame('Service Unavailable', HttpStatus::ServiceUnavailable->reasonPhrase());
    }

    #[Test]
    #[DataProvider('clientErrorCases')]
    public function is_client_error_returns_expected(HttpStatus $status, bool $expected): void
    {
        self::assertSame($expected, $status->isClientError());
    }

    #[Test]
    #[DataProvider('serverErrorCases')]
    public function is_server_error_returns_expected(HttpStatus $status, bool $expected): void
    {
        self::assertSame($expected, $status->isServerError());
    }
}
