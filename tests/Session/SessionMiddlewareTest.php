<?php

declare(strict_types=1);

namespace Vestige\Tests\Session;

use DateTimeImmutable;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;
use Vestige\Config\Config;
use Vestige\Session\Exceptions\NoActiveSessionException;
use Vestige\Session\Session;
use Vestige\Session\SessionContext;
use Vestige\Session\SessionMiddleware;
use Vestige\Session\SessionOptions;
use Vestige\Session\Storage\InMemorySessionStorage;
use Vestige\Session\Storage\SessionStorageInterface;
use Vestige\Tests\Clock\Fixtures\FrozenClock;
use Vestige\Tests\Session\Fixtures\CallbackHandler;

#[CoversClass(SessionMiddleware::class)]
final class SessionMiddlewareTest extends TestCase
{
    private const string ID = 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa';

    private FrozenClock $clock;
    private InMemorySessionStorage $storage;
    private SessionContext $context;
    private SessionMiddleware $middleware;
    private Psr17Factory $psr17;

    protected function setUp(): void
    {
        $this->clock = new FrozenClock(new DateTimeImmutable('2026-06-09 12:00:00'));
        $this->storage = new InMemorySessionStorage($this->clock);
        $this->context = new SessionContext();
        $this->middleware = new SessionMiddleware(
            $this->storage,
            $this->context,
            SessionOptions::fromConfig(new Config([])),
        );
        $this->psr17 = new Psr17Factory();
    }

    private function request(?string $cookieValue = null): ServerRequestInterface
    {
        $request = $this->psr17->createServerRequest('GET', '/');
        if ($cookieValue === null) {
            return $request;
        }

        return $request->withCookieParams(['vestige_session' => $cookieValue]);
    }

    private function handler(callable $callback): CallbackHandler
    {
        return new CallbackHandler(function (ServerRequestInterface $request) use ($callback): ResponseInterface {
            $session = $request->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE);
            if ($session instanceof Session === false) {
                self::fail('Request is missing the session attribute.');
            }

            $result = $callback($session);

            return $result instanceof ResponseInterface ? $result : $this->psr17->createResponse(200);
        });
    }

    #[Test]
    public function untouched_fresh_session_writes_nothing_and_sets_no_cookie(): void
    {
        $capturedId = null;
        $response = $this->middleware->process(
            $this->request(),
            $this->handler(function (Session $session) use (&$capturedId): void {
                $capturedId = $session->id();
            }),
        );

        self::assertFalse($response->hasHeader('Set-Cookie'));
        self::assertNull($this->storage->read((string) $capturedId));
    }

    #[Test]
    public function dirty_session_is_persisted_with_cookie(): void
    {
        $capturedId = null;
        $response = $this->middleware->process(
            $this->request(),
            $this->handler(function (Session $session) use (&$capturedId): void {
                $session->set('user', 42);
                $capturedId = $session->id();
            }),
        );

        self::assertSame(['user' => 42], $this->storage->read((string) $capturedId));
        $cookie = $response->getHeaderLine('Set-Cookie');
        self::assertStringContainsString('vestige_session=' . $capturedId, $cookie);
        self::assertStringContainsString('Max-Age=7200', $cookie);
        self::assertStringContainsString('Path=/', $cookie);
        self::assertStringContainsString('Secure', $cookie);
        self::assertStringContainsString('HttpOnly', $cookie);
        self::assertStringContainsString('SameSite=Lax', $cookie);
    }

    #[Test]
    public function clean_preexisting_session_is_touched_not_rewritten(): void
    {
        $this->storage->write(self::ID, ['user' => 42], 7200);
        $this->clock->advance(7000);

        $observed = null;
        $response = $this->middleware->process(
            $this->request(self::ID),
            $this->handler(function (Session $session) use (&$observed): void {
                $observed = $session->get('user');
            }),
        );

        self::assertSame(42, $observed);
        self::assertStringContainsString('vestige_session=' . self::ID, $response->getHeaderLine('Set-Cookie'));
        self::assertStringContainsString('Max-Age=7200', $response->getHeaderLine('Set-Cookie'));

        $this->clock->advance(7000);
        self::assertSame(['user' => 42], $this->storage->read(self::ID), 'touch must have slid the expiry');
    }

    #[Test]
    public function invalid_session_ids_produce_a_fresh_session(): void
    {
        foreach (['../../etc/passwd', 'UPPERCASE00000000000000000000000', 'short', self::ID . "\n"] as $bad) {
            $observedPreExisting = null;
            $this->middleware->process(
                $this->request($bad),
                $this->handler(function (Session $session) use (&$observedPreExisting): void {
                    $observedPreExisting = $session->isPreExisting();
                }),
            );

            self::assertFalse($observedPreExisting, sprintf('ID "%s" must not load a session', $bad));
        }
    }

    #[Test]
    public function well_formed_unknown_id_produces_a_fresh_session(): void
    {
        $observedPreExisting = null;
        $observedId = null;
        $this->middleware->process(
            $this->request(self::ID),
            $this->handler(function (Session $session) use (&$observedPreExisting, &$observedId): void {
                $observedPreExisting = $session->isPreExisting();
                $observedId = $session->id();
            }),
        );

        self::assertFalse($observedPreExisting);
        self::assertNotSame(self::ID, $observedId);
    }

    #[Test]
    public function destroy_removes_record_and_expires_cookie(): void
    {
        $this->storage->write(self::ID, ['user' => 42], 7200);

        $response = $this->middleware->process(
            $this->request(self::ID),
            $this->handler(function (Session $session): void {
                $session->destroy();
            }),
        );

        self::assertNull($this->storage->read(self::ID));
        $cookie = $response->getHeaderLine('Set-Cookie');
        self::assertStringStartsWith('vestige_session=;', $cookie);
        self::assertStringContainsString('Max-Age=0', $cookie);
    }

    #[Test]
    public function destroy_without_client_cookie_sends_no_cookie(): void
    {
        $response = $this->middleware->process(
            $this->request(),
            $this->handler(function (Session $session): void {
                $session->destroy();
            }),
        );

        self::assertFalse($response->hasHeader('Set-Cookie'));
    }

    #[Test]
    public function regenerate_swaps_records_and_sends_fresh_cookie(): void
    {
        $this->storage->write(self::ID, ['user' => 42], 7200);
        $newId = null;

        $response = $this->middleware->process(
            $this->request(self::ID),
            $this->handler(function (Session $session) use (&$newId): void {
                $session->regenerate();
                $newId = $session->id();
            }),
        );

        self::assertNull($this->storage->read(self::ID));
        self::assertSame(['user' => 42], $this->storage->read((string) $newId));
        self::assertStringContainsString('vestige_session=' . $newId, $response->getHeaderLine('Set-Cookie'));
    }

    #[Test]
    public function destroy_after_regenerate_removes_both_records(): void
    {
        $this->storage->write(self::ID, ['user' => 42], 7200);
        $newId = null;

        $response = $this->middleware->process(
            $this->request(self::ID),
            $this->handler(function (Session $session) use (&$newId): void {
                $session->regenerate();
                $newId = $session->id();
                $session->destroy();
            }),
        );

        self::assertNull($this->storage->read(self::ID));
        self::assertNull($this->storage->read((string) $newId));
        self::assertStringContainsString('Max-Age=0', $response->getHeaderLine('Set-Cookie'));
    }

    #[Test]
    public function context_is_set_during_handling_and_cleared_after(): void
    {
        $sawSession = false;
        $this->middleware->process(
            $this->request(),
            $this->handler(function () use (&$sawSession): void {
                $this->context->current();
                $sawSession = true;
            }),
        );

        self::assertTrue($sawSession);
        $this->expectException(NoActiveSessionException::class);
        $this->context->current();
    }

    #[Test]
    public function throwing_handler_clears_context_and_writes_nothing(): void
    {
        $capturedId = null;

        try {
            $this->middleware->process(
                $this->request(),
                $this->handler(function (Session $session) use (&$capturedId): void {
                    $session->set('user', 42);
                    $capturedId = $session->id();
                    throw new RuntimeException('boom');
                }),
            );
            self::fail('Exception must propagate');
        } catch (RuntimeException) {
        }

        self::assertNull($this->storage->read((string) $capturedId));
        $this->expectException(NoActiveSessionException::class);
        $this->context->current();
    }

    #[Test]
    public function gc_runs_when_divisor_is_one(): void
    {
        $middleware = new SessionMiddleware(
            $this->storage,
            $this->context,
            SessionOptions::fromConfig(new Config(['session' => ['gc' => ['divisor' => 1]]])),
        );
        $this->storage->write(self::ID, ['stale' => true], 100);
        $this->clock->advance(101);

        $middleware->process($this->request(), $this->handler(fn(): null => null));

        self::assertNull($this->storage->read(self::ID));
    }

    #[Test]
    public function gc_is_disabled_when_divisor_is_below_one(): void
    {
        $storage = new class implements SessionStorageInterface {
            public int $gcCalls = 0;

            public function read(string $id): ?array
            {
                return null;
            }

            public function write(string $id, array $data, int $ttl): void {}

            public function touch(string $id): void {}

            public function destroy(string $id): void {}

            public function gc(): void
            {
                ++$this->gcCalls;
            }
        };
        $middleware = new SessionMiddleware(
            $storage,
            $this->context,
            SessionOptions::fromConfig(new Config(['session' => ['gc' => ['divisor' => 0]]])),
        );

        $middleware->process($this->request(), $this->handler(fn(): null => null));

        self::assertSame(0, $storage->gcCalls);
    }
}
