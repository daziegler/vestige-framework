<?php

declare(strict_types=1);

namespace Vestige\Tests\Session;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Vestige\Session\Exceptions\NoActiveSessionException;
use Vestige\Session\Session;
use Vestige\Session\SessionContext;
use Vestige\Session\SessionProxy;

#[CoversClass(SessionProxy::class)]
final class SessionProxyTest extends TestCase
{
    #[Test]
    public function delegates_to_current_session(): void
    {
        $context = new SessionContext();
        $session = new Session('aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa', ['user' => 42], preExisting: true);
        $context->set($session);
        $proxy = new SessionProxy($context);

        self::assertSame(42, $proxy->get('user'));
        self::assertTrue($proxy->has('user'));
        self::assertSame('aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa', $proxy->id());
        self::assertSame(['user' => 42], $proxy->all());

        $proxy->set('role', 'admin');
        self::assertSame('admin', $session->get('role'));

        $proxy->remove('user');
        self::assertFalse($session->has('user'));

        $proxy->clear();
        self::assertSame([], $session->all());
    }

    #[Test]
    public function delegates_regenerate_and_destroy(): void
    {
        $context = new SessionContext();
        $session = new Session('aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa', [], preExisting: true);
        $context->set($session);
        $proxy = new SessionProxy($context);

        $proxy->regenerate();
        self::assertSame('aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa', $session->regeneratedFrom());

        $proxy->destroy();
        self::assertTrue($session->isDestroyed());
    }

    #[Test]
    public function use_without_active_session_throws(): void
    {
        $proxy = new SessionProxy(new SessionContext());

        $this->expectException(NoActiveSessionException::class);
        $proxy->get('anything');
    }
}
