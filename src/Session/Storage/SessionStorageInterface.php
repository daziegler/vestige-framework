<?php

declare(strict_types=1);

namespace Vestige\Session\Storage;

interface SessionStorageInterface
{
    /** @return array<string, mixed>|null */
    public function read(string $id): ?array;

    /** @param array<string, mixed> $data */
    public function write(string $id, array $data, int $ttl): void;

    public function touch(string $id): void;

    public function destroy(string $id): void;

    public function gc(): void;
}
