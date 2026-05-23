<?php

declare(strict_types=1);

namespace Vestige\Container;

interface ServiceProviderInterface
{
    public function register(ContainerInterface $container): void;
}