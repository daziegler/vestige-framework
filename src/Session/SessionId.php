<?php

declare(strict_types=1);

namespace Vestige\Session;

use Stringable;

final readonly class SessionId implements Stringable
{
    private const string PATTERN = '/^[a-f0-9]{32}\z/';

    private function __construct(public string $value) {}

    public static function generate(): self
    {
        return new self(bin2hex(random_bytes(16)));
    }

    public static function tryFrom(string $value): ?self
    {
        if (preg_match(self::PATTERN, $value) !== 1) {
            return null;
        }

        return new self($value);
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
