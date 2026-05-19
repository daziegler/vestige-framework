<?php

declare(strict_types=1);

namespace Vestige\Container;

use Psr\Container\ContainerInterface as PsrContainerInterface;

interface ContainerInterface extends PsrContainerInterface
{
    public function register(string $class): DefinitionInterface;

    public function bind(string $id, string|object|callable $concrete): DefinitionInterface;

    public function addServiceProvider(ServiceProviderInterface $provider): void;
}
