<?php

declare(strict_types=1);

namespace Vestige\Tests\Session\Exceptions;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Vestige\Session\Exceptions\InvalidSessionOptionException;
use Vestige\Session\Exceptions\NoActiveSessionException;
use Vestige\Session\Exceptions\SessionDestroyedException;
use Vestige\Session\Exceptions\SessionExceptionInterface;
use Vestige\Session\Exceptions\SessionStorageException;

#[CoversClass(InvalidSessionOptionException::class)]
#[CoversClass(NoActiveSessionException::class)]
#[CoversClass(SessionDestroyedException::class)]
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

    #[Test]
    public function invalid_id_names_the_id(): void
    {
        $exception = SessionStorageException::invalidId('../escape');

        self::assertInstanceOf(SessionExceptionInterface::class, $exception);
        self::assertStringContainsString('../escape', $exception->getMessage());
    }

    #[Test]
    public function session_destroyed_implements_marker(): void
    {
        $exception = SessionDestroyedException::create();

        self::assertInstanceOf(SessionExceptionInterface::class, $exception);
        self::assertStringContainsString('destroyed', $exception->getMessage());
    }

    #[Test]
    public function invalid_session_option_names_key_and_types(): void
    {
        $exception = InvalidSessionOptionException::forKey('session.lifetime', 'int', 'soon');

        self::assertInstanceOf(SessionExceptionInterface::class, $exception);
        self::assertStringContainsString('session.lifetime', $exception->getMessage());
        self::assertStringContainsString('int', $exception->getMessage());
        self::assertStringContainsString('string', $exception->getMessage());
    }
}
