<?php

declare(strict_types=1);

namespace Vestige\Session;

final class Session implements SessionInterface
{
    private bool $dirty = false;
    private bool $destroyed = false;
    private ?string $regeneratedFrom = null;

    /** @param array<string, mixed> $data */
    public function __construct(
        private string $id,
        private array $data,
        private readonly bool $preExisting,
    ) {}

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->data[$key] ?? $default;
    }

    public function set(string $key, mixed $value): void
    {
        $this->data[$key] = $value;
        $this->dirty = true;
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->data);
    }

    public function remove(string $key): void
    {
        if (array_key_exists($key, $this->data) === false) {
            return;
        }

        unset($this->data[$key]);
        $this->dirty = true;
    }

    public function clear(): void
    {
        if ($this->data === []) {
            return;
        }

        $this->data = [];
        $this->dirty = true;
    }

    public function all(): array
    {
        return $this->data;
    }

    public function id(): string
    {
        return $this->id;
    }

    public function regenerate(): void
    {
        $this->regeneratedFrom ??= $this->id;
        $this->id = bin2hex(random_bytes(16));
    }

    public function destroy(): void
    {
        $this->data = [];
        $this->destroyed = true;
    }

    public function isDirty(): bool
    {
        return $this->dirty;
    }

    public function isDestroyed(): bool
    {
        return $this->destroyed;
    }

    public function isPreExisting(): bool
    {
        return $this->preExisting;
    }

    public function regeneratedFrom(): ?string
    {
        return $this->regeneratedFrom;
    }
}