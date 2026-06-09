<?php

declare(strict_types=1);

namespace Vestige\Tests\Session\Exceptions;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Vestige\Session\Exceptions\NoActiveSessionException;
use Vestige\Session\Exceptions\SessionExceptionInterface;
use Vestige\Session\Exceptions\SessionStorageException;

#[CoversClass(NoActiveSessionException::class)]
#[CoversClass(SessionStorageException::class)]
final class SessionExceptionsTest extends TestCase
{
    #[Test]
    public function no_active_session_implements_marker(): void
    {
        $exception = NoActiveSessionException::create();

        self::assertInstanceOf(SessionExceptionInterface::class, $exception);
        self::assertStringContainsString('No active session', $exception->getMessage());
    }

    #[Test]
    public function not_writable_names_the_directory(): void
    {
        $exception = SessionStorageException::notWritable('/tmp/foo');

        self::assertInstanceOf(SessionExceptionInterface::class, $exception);
        self::assertStringContainsString('/tmp/foo', $exception->getMessage());
    }

    #[Test]
    public function write_failed_names_the_path(): void
    {
        $exception = SessionStorageException::writeFailed('/tmp/foo/bar');

        self::assertStringContainsString('/tmp/foo/bar', $exception->getMessage());
    }
}