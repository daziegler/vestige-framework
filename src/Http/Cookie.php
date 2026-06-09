<?php

declare(strict_types=1);

namespace Vestige\Http;

use InvalidArgumentException;
use Stringable;

final readonly class Cookie implements Stringable
{
    public function __construct(
        public string $name,
        public string $value,
        public int $maxAge,
        public string $path,
        public ?string $domain,
        public bool $secure,
        public bool $httpOnly,
        public SameSite $sameSite,
    ) {
        if ($this->sameSite === SameSite::None && $this->secure === false) {
            throw new InvalidArgumentException('SameSite=None requires the Secure attribute.');
        }
    }

    public static function expired(
        string $name,
        string $path,
        ?string $domain,
        bool $secure,
        bool $httpOnly,
        SameSite $sameSite,
    ): self {
        return new self($name, '', 0, $path, $domain, $secure, $httpOnly, $sameSite);
    }

    public function __toString(): string
    {
        $parts = [
            sprintf('%s=%s', $this->name, $this->value),
            sprintf('Max-Age=%d', $this->maxAge),
            sprintf('Path=%s', $this->path),
        ];

        if ($this->domain !== null) {
            $parts[] = sprintf('Domain=%s', $this->domain);
        }

        if ($this->secure) {
            $parts[] = 'Secure';
        }

        if ($this->httpOnly) {
            $parts[] = 'HttpOnly';
        }

        $parts[] = sprintf('SameSite=%s', $this->sameSite->value);

        return implode('; ', $parts);
    }
}
