<?php

declare(strict_types=1);

namespace Vestige\Container;

use League\Container\Definition\DefinitionInterface as LeagueDefinitionInterface;

final readonly class Definition implements DefinitionInterface
{
    public function __construct(private LeagueDefinitionInterface $delegate) {}

    public function shared(): self
    {
        $this->delegate->setShared(true);

        return $this;
    }
}
