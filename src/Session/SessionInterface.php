<?php

declare(strict_types=1);

namespace Vestige\Session;

interface SessionInterface
{
    public function get(string $key, mixed $default = null): mixed;

    public function set(string $key, mixed $value): void;

    public function has(string $key): bool;

    public function remove(string $key): void;

    public function clear(): void;

    /** @return array<string, mixed> */
    public function all(): array;

    public function id(): string;

    public function regenerate(): void;

    public function destroy(): void;
}
