<?php

declare(strict_types=1);

namespace Vestige\Tests\Container;

use League\Container\Container as LeagueContainer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Vestige\Container\Container;
use Vestige\Container\Definition;
use Vestige\Tests\Container\Fixtures\SimpleService;
use Vestige\Tests\Container\Fixtures\SimpleServiceProvider;

#[CoversClass(Container::class)]
#[CoversClass(Definition::class)]
final class ContainerTest extends TestCase
{
    #[Test]
    public function register_resolves_class_by_autowiring(): void
    {
        $container = new Container(new LeagueContainer());
        $container->register(SimpleService::class);

        self::assertInstanceOf(SimpleService::class, $container->get(SimpleService::class));
    }

    #[Test]
    public function has_reports_registered_ids(): void
    {
        $container = new Container(new LeagueContainer());
        $container->register(SimpleService::class);

        self::assertTrue($container->has(SimpleService::class));
        self::assertFalse($container->has('not.registered'));
    }

    #[Test]
    public function bind_returns_provided_object_instance(): void
    {
        $container = new Container(new LeagueContainer());
        $instance = new SimpleService();
        $container->bind('service', $instance);

        self::assertSame($instance, $container->get('service'));
    }

    #[Test]
    public function shared_definition_returns_same_instance_on_repeat_get(): void
    {
        $container = new Container(new LeagueContainer());
        $container->register(SimpleService::class)->shared();

        $first = $container->get(SimpleService::class);
        $second = $container->get(SimpleService::class);

        self::assertSame($first, $second);
    }

    #[Test]
    public function add_service_provider_registers_its_services(): void
    {
        $container = new Container(new LeagueContainer());
        $container->addServiceProvider(new SimpleServiceProvider());

        self::assertInstanceOf(SimpleService::class, $container->get(SimpleService::class));
    }
}
