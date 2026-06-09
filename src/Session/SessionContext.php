<?php

declare(strict_types=1);

namespace Vestige\Session;

use Vestige\Session\Exceptions\NoActiveSessionException;

final class SessionContext
{
    private ?Session $current = null;

    public function set(Session $session): void
    {
        $this->current = $session;
    }

    public function clear(): void
    {
        $this->current = null;
    }

    public function current(): Session
    {
        if ($this->current === null) {
            throw NoActiveSessionException::create();
        }

        return $this->current;
    }
}
