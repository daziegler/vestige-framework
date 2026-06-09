<?php

declare(strict_types=1);

namespace Vestige\Tests\Session\Storage;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Vestige\Session\Exceptions\SessionStorageException;
use Vestige\Session\Storage\FileSessionStorage;
use Vestige\Session\Storage\SessionStorageInterface;
use Vestige\Tests\Clock\Fixtures\FrozenClock;

#[CoversClass(FileSessionStorage::class)]
final class FileSessionStorageTest extends SessionStorageContractTestCase
{
    private const string ID = 'cccccccccccccccccccccccccccccccc';

    private string $dir;

    protected function createStorage(FrozenClock $clock): SessionStorageInterface
    {
        $this->dir = sys_get_temp_dir() . '/vestige_test_' . bin2hex(random_bytes(8));

        return new FileSessionStorage($this->dir, $clock);
    }

    protected function tearDown(): void
    {
        if (is_dir($this->dir) === false) {
            return;
        }

        foreach (glob($this->dir . '/*') ?: [] as $file) {
            unlink($file);
        }

        rmdir($this->dir);
    }

    #[Test]
    public function corrupt_json_reads_as_null(): void
    {
        $this->storage->write(self::ID, ['user' => 42], 100);
        file_put_contents($this->dir . '/' . self::ID, '{not json');

        self::assertNull($this->storage->read(self::ID));
    }

    #[Test]
    public function valid_json_that_is_not_an_array_reads_as_null(): void
    {
        $this->storage->write(self::ID, ['user' => 42], 100);
        file_put_contents($this->dir . '/' . self::ID, '"5"');

        self::assertNull($this->storage->read(self::ID));
    }

    #[Test]
    public function payload_without_envelope_reads_as_null(): void
    {
        $this->storage->write(self::ID, ['user' => 42], 100);
        file_put_contents($this->dir . '/' . self::ID, '{"user": 42}');

        self::assertNull($this->storage->read(self::ID));
    }

    #[Test]
    public function directory_is_created_private(): void
    {
        $this->storage->write(self::ID, ['user' => 42], 100);

        self::assertSame('0700', substr(sprintf('%o', fileperms($this->dir)), -4));
    }

    #[Test]
    public function session_files_are_private(): void
    {
        $this->storage->write(self::ID, ['user' => 42], 100);

        self::assertSame('0600', substr(sprintf('%o', fileperms($this->dir . '/' . self::ID)), -4));
    }

    #[Test]
    public function unwritable_directory_throws(): void
    {
        $storage = new FileSessionStorage('/proc/vestige-cannot-exist', $this->clock);

        $this->expectException(SessionStorageException::class);
        $storage->write(self::ID, [], 100);
    }

    #[Test]
    public function unencodable_data_throws(): void
    {
        $this->expectException(SessionStorageException::class);
        $this->storage->write(self::ID, ['bad' => "\xB1\x31"], 100);
    }

    #[Test]
    public function gc_on_missing_directory_is_a_noop(): void
    {
        $storage = new FileSessionStorage($this->dir . '/missing', $this->clock);

        $storage->gc();

        self::assertDirectoryDoesNotExist($this->dir . '/missing');
    }
}
