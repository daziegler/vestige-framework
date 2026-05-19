<?php

declare(strict_types=1);

namespace Vestige\Tests\Container\Fixtures;

use League\Container\ServiceProvider\AbstractServiceProvider;
use Vestige\Container\ServiceProviderInterface;

final class SimpleServiceProvider extends AbstractServiceProvider implements ServiceProviderInterface
{
    public function provides(string $id): bool
    {
        return $id === SimpleService::class;
    }

    public function register(): void
    {
        $this->getContainer()->add(SimpleService::class);
    }
}
