<?php

declare(strict_types=1);

namespace Vestige\Session\Storage;

use Psr\Clock\ClockInterface;

final class InMemorySessionStorage implements SessionStorageInterface
{
    /** @var array<string, array{data: array<string, mixed>, ttl: int, touchedAt: int}> */
    private array $records = [];

    public function __construct(private readonly ClockInterface $clock) {}

    public function read(string $id): ?array
    {
        $record = $this->records[$id] ?? null;
        if ($record === null) {
            return null;
        }

        if ($this->expired($record)) {
            unset($this->records[$id]);

            return null;
        }

        return $record['data'];
    }

    public function write(string $id, array $data, int $ttl): void
    {
        $this->records[$id] = [
            'data' => $data,
            'ttl' => $ttl,
            'touchedAt' => $this->clock->now()->getTimestamp(),
        ];
    }

    public function touch(string $id): void
    {
        if (isset($this->records[$id]) === false) {
            return;
        }

        $this->records[$id]['touchedAt'] = $this->clock->now()->getTimestamp();
    }

    public function destroy(string $id): void
    {
        unset($this->records[$id]);
    }

    public function gc(): void
    {
        foreach ($this->records as $id => $record) {
            if ($this->expired($record)) {
                unset($this->records[$id]);
            }
        }
    }

    /** @param array{data: array<string, mixed>, ttl: int, touchedAt: int} $record */
    private function expired(array $record): bool
    {
        return $record['touchedAt'] + $record['ttl'] < $this->clock->now()->getTimestamp();
    }
}
