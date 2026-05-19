<?php

declare(strict_types=1);

namespace Vestige\Tests\Http\Exceptions;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Vestige\Http\Exceptions\ConflictException;
use Vestige\Http\Exceptions\ForbiddenException;
use Vestige\Http\Exceptions\HttpException;
use Vestige\Http\Exceptions\MethodNotAllowedException;
use Vestige\Http\Exceptions\NotFoundException;
use Vestige\Http\Exceptions\UnauthorizedException;
use Vestige\Http\Exceptions\UnprocessableEntityException;
use Vestige\Http\HttpMethod;
use Vestige\Http\HttpStatus;

#[CoversClass(HttpException::class)]
#[CoversClass(NotFoundException::class)]
#[CoversClass(MethodNotAllowedException::class)]
#[CoversClass(UnauthorizedException::class)]
#[CoversClass(ForbiddenException::class)]
#[CoversClass(ConflictException::class)]
#[CoversClass(UnprocessableEntityException::class)]
final class HttpExceptionTest extends TestCase
{
    #[Test]
    public function not_found_exception_has_status_404(): void
    {
        $exception = new NotFoundException();

        self::assertSame(HttpStatus::NotFound, $exception->getStatusCode());
    }

    #[Test]
    public function unauthorized_exception_has_status_401(): void
    {
        self::assertSame(HttpStatus::Unauthorized, (new UnauthorizedException())->getStatusCode());
    }

    #[Test]
    public function forbidden_exception_has_status_403(): void
    {
        self::assertSame(HttpStatus::Forbidden, (new ForbiddenException())->getStatusCode());
    }

    #[Test]
    public function conflict_exception_has_status_409(): void
    {
        self::assertSame(HttpStatus::Conflict, (new ConflictException())->getStatusCode());
    }

    #[Test]
    public function unprocessable_entity_exception_has_status_422(): void
    {
        self::assertSame(HttpStatus::UnprocessableEntity, (new UnprocessableEntityException())->getStatusCode());
    }

    #[Test]
    public function method_not_allowed_exception_has_status_405(): void
    {
        $exception = new MethodNotAllowedException([HttpMethod::Get]);

        self::assertSame(HttpStatus::MethodNotAllowed, $exception->getStatusCode());
    }

    #[Test]
    public function default_message_is_status_reason_phrase(): void
    {
        $exception = new NotFoundException();

        self::assertSame('Not Found', $exception->getMessage());
    }

    #[Test]
    public function custom_message_overrides_default(): void
    {
        $exception = new NotFoundException('User not found');

        self::assertSame('User not found', $exception->getMessage());
    }

    #[Test]
    public function exception_code_matches_status_value(): void
    {
        $exception = new NotFoundException();

        self::assertSame(404, $exception->getCode());
    }

    #[Test]
    public function headers_default_to_empty(): void
    {
        $exception = new NotFoundException();

        self::assertSame([], $exception->getHeaders());
    }

    #[Test]
    public function custom_headers_are_returned(): void
    {
        $exception = new NotFoundException(headers: ['X-Custom' => 'value']);

        self::assertSame(['X-Custom' => 'value'], $exception->getHeaders());
    }

    #[Test]
    public function method_not_allowed_builds_allow_header_from_methods(): void
    {
        $exception = new MethodNotAllowedException([HttpMethod::Get, HttpMethod::Post]);

        self::assertSame(['Allow' => 'GET, POST'], $exception->getHeaders());
    }
}
