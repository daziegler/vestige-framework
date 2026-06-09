<?php

declare(strict_types=1);

namespace Vestige\Tests\Http\Problem;

use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Vestige\Http\Exceptions\NotFoundException;
use Vestige\Http\Exceptions\UnprocessableEntityException;
use Vestige\Http\HttpStatus;
use Vestige\Http\Problem\Problem;
use Vestige\Http\Problem\Rfc9457;

#[CoversClass(Problem::class)]
#[CoversClass(Rfc9457::class)]
final class ProblemTest extends TestCase
{
    #[Test]
    public function for_status_derives_title_from_reason_phrase(): void
    {
        $problem = Problem::forStatus(HttpStatus::NotFound);

        self::assertSame(HttpStatus::NotFound, $problem->getStatus());
        self::assertSame('Not Found', $problem->getTitle());
        self::assertSame(Rfc9457::DEFAULT_TYPE, $problem->getType());
    }

    #[Test]
    public function to_array_contains_required_members_only_when_minimal(): void
    {
        $problem = Problem::forStatus(HttpStatus::NotFound);

        self::assertSame(
            ['type' => 'about:blank', 'title' => 'Not Found', 'status' => 404],
            $problem->toArray(),
        );
    }

    #[Test]
    public function to_array_includes_detail_and_instance_when_present(): void
    {
        $problem = new Problem(
            status: HttpStatus::UnprocessableEntity,
            title: 'Unprocessable Entity',
            detail: 'The name field is required.',
            instance: '/users',
        );

        $array = $problem->toArray();

        self::assertSame('The name field is required.', $array['detail']);
        self::assertSame('/users', $array['instance']);
    }

    #[Test]
    public function from_http_exception_uses_public_message_as_detail_when_available(): void
    {
        $request = new Psr17Factory()->createServerRequest('GET', '/widgets/9');
        $exception = new UnprocessableEntityException();

        $problem = Problem::fromHttpException($exception, $request);

        self::assertSame(HttpStatus::UnprocessableEntity, $problem->getStatus());
        self::assertSame('/widgets/9', $problem->getInstance());
        // UnprocessableEntityException does not implement PublicMessageInterface → no detail leak
        self::assertNull($problem->getDetail());
    }

    #[Test]
    public function from_http_exception_sets_instance_to_request_path(): void
    {
        $request = new Psr17Factory()->createServerRequest('GET', '/missing');

        $problem = Problem::fromHttpException(new NotFoundException(), $request);

        self::assertSame('/missing', $problem->getInstance());
    }

    #[Test]
    public function from_http_exception_omits_instance_for_an_empty_path(): void
    {
        $factory = new Psr17Factory();
        $request = $factory->createServerRequest('GET', $factory->createUri(''));

        $problem = Problem::fromHttpException(new NotFoundException(), $request);

        self::assertNull($problem->getInstance());
        self::assertArrayNotHasKey('instance', $problem->toArray());
    }

    #[Test]
    public function with_extension_returns_a_new_instance_with_the_member_appended(): void
    {
        $problem = Problem::forStatus(HttpStatus::NotFound);

        $extended = $problem->withExtension('traceId', 'abc-123');

        self::assertArrayNotHasKey('traceId', $problem->toArray());
        self::assertSame('abc-123', $extended->toArray()['traceId']);
    }

    #[Test]
    public function get_extensions_returns_the_extension_members(): void
    {
        $problem = Problem::forStatus(HttpStatus::NotFound);

        self::assertSame([], $problem->getExtensions());
        self::assertSame(
            ['traceId' => 'abc-123'],
            $problem->withExtension('traceId', 'abc-123')->getExtensions(),
        );
    }
}
