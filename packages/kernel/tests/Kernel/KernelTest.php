<?php

declare(strict_types=1);

namespace Vestige\Tests\Kernel;

use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Vestige\Exceptions\KernelNotBootedException;
use Vestige\Kernel;

#[CoversClass(Kernel::class)]
#[CoversClass(KernelNotBootedException::class)]
final class KernelTest extends TestCase
{
    #[Test]
    public function boot_succeeds_on_empty_config_app(): void
    {
        $kernel = new Kernel(__DIR__ . '/fixtures/empty-app');
        $kernel->boot();

        self::expectNotToPerformAssertions();
    }

    #[Test]
    public function handle_throws_when_not_booted(): void
    {
        $kernel = new Kernel(__DIR__ . '/fixtures/empty-app');
        $request = (new Psr17Factory())->createServerRequest('GET', '/');

        $this->expectException(KernelNotBootedException::class);
        $kernel->handle($request);
    }

    #[Test]
    public function boot_is_idempotent(): void
    {
        $kernel = new Kernel(__DIR__ . '/fixtures/empty-app');
        $kernel->boot();
        $kernel->boot();

        self::expectNotToPerformAssertions();
    }
}
