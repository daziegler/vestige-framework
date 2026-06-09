<?php

declare(strict_types=1);

namespace Vestige\Session;

final readonly class SessionProxy implements SessionInterface
{
    public function __construct(private SessionContext $context) {}

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->context->current()->get($key, $default);
    }

    public function set(string $key, mixed $value): void
    {
        $this->context->current()->set($key, $value);
    }

    public function has(string $key): bool
    {
        return $this->context->current()->has($key);
    }

    public function remove(string $key): void
    {
        $this->context->current()->remove($key);
    }

    public function clear(): void
    {
        $this->context->current()->clear();
    }

    public function all(): array
    {
        return $this->context->current()->all();
    }

    public function id(): string
    {
        return $this->context->current()->id();
    }

    public function regenerate(): void
    {
        $this->context->current()->regenerate();
    }

    public function destroy(): void
    {
        $this->context->current()->destroy();
    }
}