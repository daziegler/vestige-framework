<?php

declare(strict_types=1);

namespace Vestige\Tests\Session;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Vestige\Session\Session;

#[CoversClass(Session::class)]
final class SessionTest extends TestCase
{
    private const string ID = 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa';

    #[Test]
    public function fresh_session_is_clean(): void
    {
        $session = new Session(self::ID, [], preExisting: false);

        self::assertFalse($session->isDirty());
        self::assertFalse($session->isDestroyed());
        self::assertFalse($session->isPreExisting());
        self::assertNull($session->regeneratedFrom());
        self::assertSame(self::ID, $session->id());
    }

    #[Test]
    public function get_returns_default_for_missing_key(): void
    {
        $session = new Session(self::ID, [], preExisting: false);

        self::assertNull($session->get('missing'));
        self::assertSame('fallback', $session->get('missing', 'fallback'));
    }

    #[Test]
    public function set_stores_and_marks_dirty(): void
    {
        $session = new Session(self::ID, [], preExisting: true);

        $session->set('user', 42);

        self::assertSame(42, $session->get('user'));
        self::assertTrue($session->has('user'));
        self::assertTrue($session->isDirty());
        self::assertSame(['user' => 42], $session->all());
    }

    #[Test]
    public function remove_unsets_and_marks_dirty(): void
    {
        $session = new Session(self::ID, ['user' => 42], preExisting: true);

        $session->remove('user');

        self::assertFalse($session->has('user'));
        self::assertTrue($session->isDirty());
    }

    #[Test]
    public function remove_of_missing_key_stays_clean(): void
    {
        $session = new Session(self::ID, [], preExisting: true);

        $session->remove('missing');

        self::assertFalse($session->isDirty());
    }

    #[Test]
    public function clear_empties_and_marks_dirty(): void
    {
        $session = new Session(self::ID, ['a' => 1, 'b' => 2], preExisting: true);

        $session->clear();

        self::assertSame([], $session->all());
        self::assertTrue($session->isDirty());
    }

    #[Test]
    public function clear_of_empty_session_stays_clean(): void
    {
        $session = new Session(self::ID, [], preExisting: true);

        $session->clear();

        self::assertFalse($session->isDirty());
    }

    #[Test]
    public function regenerate_swaps_id_keeps_data_and_remembers_origin(): void
    {
        $session = new Session(self::ID, ['user' => 42], preExisting: true);

        $session->regenerate();

        self::assertNotSame(self::ID, $session->id());
        self::assertMatchesRegularExpression('/^[a-f0-9]{32}\z/', $session->id());
        self::assertSame(self::ID, $session->regeneratedFrom());
        self::assertSame(42, $session->get('user'));
    }

    #[Test]
    public function double_regenerate_keeps_original_origin(): void
    {
        $session = new Session(self::ID, [], preExisting: true);

        $session->regenerate();
        $session->regenerate();

        self::assertSame(self::ID, $session->regeneratedFrom());
    }

    #[Test]
    public function destroy_clears_data_and_marks_destroyed(): void
    {
        $session = new Session(self::ID, ['user' => 42], preExisting: true);

        $session->destroy();

        self::assertTrue($session->isDestroyed());
        self::assertSame([], $session->all());
    }
}