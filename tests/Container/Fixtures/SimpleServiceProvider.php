<?php

declare(strict_types=1);

namespace Vestige\Tests\Container\Fixtures;

use Vestige\Container\ContainerInterface;
use Vestige\Container\ServiceProviderInterface;

final class SimpleServiceProvider implements ServiceProviderInterface
{
    public function register(ContainerInterface $container): void
    {
        $container->register(SimpleService::class);
    }
}
