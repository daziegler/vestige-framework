<?php

declare(strict_types=1);

namespace Vestige\Tests\Session\Storage;

use PHPUnit\Framework\Attributes\CoversClass;
use Vestige\Session\Storage\InMemorySessionStorage;
use Vestige\Session\Storage\SessionStorageInterface;
use Vestige\Tests\Clock\Fixtures\FrozenClock;

#[CoversClass(InMemorySessionStorage::class)]
final class InMemorySessionStorageTest extends SessionStorageContractTestCase
{
    protected function createStorage(FrozenClock $clock): SessionStorageInterface
    {
        return new InMemorySessionStorage($clock);
    }
}
