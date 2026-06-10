<?php

declare(strict_types=1);

namespace Vestige\Tests\Session\Storage;

use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Vestige\Session\Storage\SessionStorageInterface;
use Vestige\Tests\Clock\Fixtures\FrozenClock;

abstract class SessionStorageContractTestCase extends TestCase
{
    private const string ID = 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa';
    private const string OTHER_ID = 'bbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbb';

    protected FrozenClock $clock;
    protected SessionStorageInterface $storage;

    abstract protected function createStorage(FrozenClock $clock): SessionStorageInterface;

    protected function setUp(): void
    {
        $this->clock = new FrozenClock(new DateTimeImmutable('2026-06-09 12:00:00'));
        $this->storage = $this->createStorage($this->clock);
    }

    #[Test]
    public function read_of_missing_id_returns_null(): void
    {
        self::assertNull($this->storage->read(self::ID));
    }

    #[Test]
    public function write_then_read_roundtrips(): void
    {
        $this->storage->write(self::ID, ['user' => 42], 100);

        self::assertSame(['user' => 42], $this->storage->read(self::ID));
    }

    #[Test]
    public function write_overwrites_existing_record(): void
    {
        $this->storage->write(self::ID, ['a' => 1], 100);
        $this->storage->write(self::ID, ['b' => 2], 100);

        self::assertSame(['b' => 2], $this->storage->read(self::ID));
    }

    #[Test]
    public function read_past_ttl_returns_null(): void
    {
        $this->storage->write(self::ID, ['user' => 42], 100);

        $this->clock->advance(101);

        self::assertNull($this->storage->read(self::ID));
    }

    #[Test]
    public function read_is_pure_and_does_not_slide_expiry(): void
    {
        $this->storage->write(self::ID, ['user' => 42], 100);

        $this->clock->advance(60);
        self::assertNotNull($this->storage->read(self::ID));

        $this->clock->advance(60);
        self::assertNull($this->storage->read(self::ID));
    }

    #[Test]
    public function touch_slides_the_expiry(): void
    {
        $this->storage->write(self::ID, ['user' => 42], 100);

        $this->clock->advance(60);
        $this->storage->touch(self::ID);

        $this->clock->advance(60);
        self::assertSame(['user' => 42], $this->storage->read(self::ID));
    }

    #[Test]
    public function touch_of_missing_id_is_a_noop(): void
    {
        $this->storage->touch(self::ID);

        self::assertNull($this->storage->read(self::ID));
    }

    #[Test]
    public function destroy_removes_the_record(): void
    {
        $this->storage->write(self::ID, ['user' => 42], 100);

        $this->storage->destroy(self::ID);

        self::assertNull($this->storage->read(self::ID));
    }

    #[Test]
    public function destroy_of_missing_id_is_a_noop(): void
    {
        $this->storage->destroy(self::ID);

        self::assertNull($this->storage->read(self::ID));
    }

    #[Test]
    public function gc_removes_expired_and_keeps_live_records(): void
    {
        $this->storage->write(self::ID, ['old' => true], 100);
        $this->clock->advance(50);
        $this->storage->write(self::OTHER_ID, ['new' => true], 100);
        $this->clock->advance(60);

        $this->storage->gc();

        self::assertNull($this->storage->read(self::ID));
        self::assertSame(['new' => true], $this->storage->read(self::OTHER_ID));
    }
}
