<?php

declare(strict_types=1);

namespace Vestige\Session\Storage;

use FilesystemIterator;
use Psr\Clock\ClockInterface;
use SplFileInfo;
use Vestige\Session\Exceptions\SessionStorageException;
use Vestige\Session\SessionId;

final readonly class FileSessionStorage implements SessionStorageInterface
{
    public function __construct(
        private string $dir,
        private ClockInterface $clock,
    ) {}

    public function read(string $id): ?array
    {
        $path = $this->path($id);
        if (is_file($path) === false) {
            return null;
        }

        $raw = file_get_contents($path);
        if ($raw === false) {
            return null;
        }

        $payload = $this->decode($raw);
        if ($payload === null) {
            return null;
        }

        if ($this->expired($path, $payload['ttl'])) {
            $this->removeIfExists($path);

            return null;
        }

        return $payload['data'];
    }

    public function write(string $id, array $data, int $ttl): void
    {
        $path = $this->path($id);
        $this->ensureDir();

        $json = json_encode(['ttl' => $ttl, 'data' => $data]);
        if ($json === false) {
            throw SessionStorageException::writeFailed($path);
        }

        $tmp = tempnam($this->dir, 'tmp');
        if ($tmp === false || file_put_contents($tmp, $json) === false) {
            throw SessionStorageException::writeFailed($path);
        }

        touch($tmp, $this->clock->now()->getTimestamp());
        if (rename($tmp, $path) === false) {
            $this->removeIfExists($tmp);

            throw SessionStorageException::writeFailed($path);
        }
    }

    public function touch(string $id): void
    {
        $path = $this->path($id);
        if (is_file($path) === false) {
            return;
        }

        touch($path, $this->clock->now()->getTimestamp());
    }

    public function destroy(string $id): void
    {
        $this->removeIfExists($this->path($id));
    }

    public function gc(): void
    {
        if (is_dir($this->dir) === false) {
            return;
        }

        foreach (new FilesystemIterator($this->dir) as $file) {
            /** @var SplFileInfo $file */
            $path = $file->getPathname();
            $raw = @file_get_contents($path);
            $payload = $raw === false ? null : $this->decode($raw);

            if ($payload === null || $this->expired($path, $payload['ttl'])) {
                $this->removeIfExists($path);
            }
        }
    }

    private function expired(string $path, int $ttl): bool
    {
        clearstatcache(true, $path);
        $mtime = @filemtime($path);

        return $mtime === false || $mtime + $ttl < $this->clock->now()->getTimestamp();
    }

    /** @return array{ttl: int, data: array<string, mixed>}|null */
    private function decode(string $raw): ?array
    {
        $decoded = json_decode($raw, true);
        if (is_array($decoded) === false) {
            return null;
        }

        $ttl = $decoded['ttl'] ?? null;
        $data = $decoded['data'] ?? null;
        if (is_int($ttl) === false || is_array($data) === false) {
            return null;
        }

        /** @var array<string, mixed> $data */
        return ['ttl' => $ttl, 'data' => $data];
    }

    private function path(string $id): string
    {
        if (SessionId::tryFrom($id) === null) {
            throw SessionStorageException::invalidId($id);
        }

        return $this->dir . '/' . $id;
    }

    private function removeIfExists(string $path): void
    {
        @unlink($path);
    }

    private function ensureDir(): void
    {
        if (is_dir($this->dir)) {
            return;
        }

        @mkdir($this->dir, 0700, true);

        if (is_dir($this->dir) === false || is_writable($this->dir) === false) {
            throw SessionStorageException::notWritable($this->dir);
        }
    }
}
