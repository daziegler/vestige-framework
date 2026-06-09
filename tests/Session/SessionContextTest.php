<?php

declare(strict_types=1);

namespace Vestige\Tests\Session;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Vestige\Session\Exceptions\NoActiveSessionException;
use Vestige\Session\Session;
use Vestige\Session\SessionContext;

#[CoversClass(SessionContext::class)]
final class SessionContextTest extends TestCase
{
    #[Test]
    public function current_without_session_throws(): void
    {
        $this->expectException(NoActiveSessionException::class);

        new SessionContext()->current();
    }

    #[Test]
    public function current_returns_the_set_session(): void
    {
        $context = new SessionContext();
        $session = new Session('aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa', [], preExisting: false);

        $context->set($session);

        self::assertSame($session, $context->current());
    }

    #[Test]
    public function clear_removes_the_session(): void
    {
        $context = new SessionContext();
        $context->set(new Session('aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa', [], preExisting: false));

        $context->clear();

        $this->expectException(NoActiveSessionException::class);
        $context->current();
    }
}