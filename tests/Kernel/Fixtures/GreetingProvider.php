<?php

declare(strict_types=1);

namespace Vestige\Tests\Kernel\Fixtures;

use Vestige\Container\ContainerInterface;
use Vestige\Container\ServiceProviderInterface;

final class GreetingProvider implements ServiceProviderInterface
{
    public function register(ContainerInterface $container): void
    {
        $container->bind(Greeting::class, new Greeting('Hello from provider'));
    }
}
