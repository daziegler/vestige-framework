<?php

declare(strict_types=1);

namespace Vestige\Tests\Kernel;

use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Vestige\Kernel;

#[CoversClass(Kernel::class)]
final class Psr17BindingTest extends TestCase
{
    #[Test]
    public function controllers_can_autowire_the_psr17_response_factory(): void
    {
        $kernel = new Kernel(__DIR__ . '/apps/psr17-app');
        $kernel->boot();

        $request = new Psr17Factory()->createServerRequest('GET', '/');
        $response = $kernel->handle($request);

        self::assertSame(204, $response->getStatusCode());
    }
}
