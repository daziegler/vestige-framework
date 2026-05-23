<?php

declare(strict_types=1);

namespace Vestige\Container;

use League\Container\Container as LeagueContainer;

final readonly class Container implements ContainerInterface
{
    public function __construct(private LeagueContainer $delegate) {}

    public function get(string $id): mixed
    {
        return $this->delegate->get($id);
    }

    public function has(string $id): bool
    {
        return $this->delegate->has($id);
    }

    public function register(string $class): DefinitionInterface
    {
        $leagueDefinition = $this->delegate->add($class);

        return new Definition($leagueDefinition);
    }

    public function bind(string $id, string|object|callable $concrete): DefinitionInterface
    {
        $leagueDefinition = $this->delegate->add($id, $concrete);

        return new Definition($leagueDefinition);
    }
}
