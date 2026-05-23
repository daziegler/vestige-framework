<?php

declare(strict_types=1);

namespace Vestige\Container;

use Psr\Container\ContainerInterface as PsrContainerInterface;

interface ContainerInterface extends PsrContainerInterface
{
    /**
     * @template T of object
     * @param class-string<T>|string $id
     * @return ($id is class-string<T> ? T : mixed)
     */
    public function get(string $id): mixed;

    public function register(string $class): DefinitionInterface;

    public function bind(string $id, string|object|callable $concrete): DefinitionInterface;
}
