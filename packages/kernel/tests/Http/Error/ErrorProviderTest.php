<?php

declare(strict_types=1);

namespace Vestige\Tests\Http\Error;

use League\Container\Container as LeagueContainer;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use RuntimeException;
use Vestige\Container\Container;
use Vestige\Container\ServiceProviderInterface;
use Vestige\Http\Error\DevErrorProvider;
use Vestige\Http\Error\ErrorRendererInterface;
use Vestige\Http\Error\ProdErrorProvider;

#[CoversClass(ProdErrorProvider::class)]
#[CoversClass(DevErrorProvider::class)]
final class ErrorProviderTest extends TestCase
{
    #[Test]
    public function prod_provider_hides_debug_details(): void
    {
        $container = $this->containerWith(new ProdErrorProvider());
        $renderer = $container->get(ErrorRendererInterface::class);

        self::assertInstanceOf(ErrorRendererInterface::class, $renderer);
        $response = $renderer->render(new RuntimeException('the real cause'), $this->jsonRequest());

        $data = json_decode((string) $response->getBody(), true);
        self::assertIsArray($data);
        self::assertSame(500, $data['status']);
        self::assertArrayNotHasKey('exception', $data);
        self::assertArrayNotHasKey('message', $data);
    }

    private function containerWith(ServiceProviderInterface $provider): Container
    {
        $factory = new Psr17Factory();
        $container = new Container(new LeagueContainer());
        $container->bind(ResponseFactoryInterface::class, $factory);
        $container->bind(StreamFactoryInterface::class, $factory);

        $provider->register($container);

        return $container;
    }

    private function jsonRequest(): ServerRequestInterface
    {
        return new Psr17Factory()->createServerRequest('GET', '/x')->withHeader('Accept', 'application/json');
    }

    #[Test]
    public function dev_provider_exposes_debug_details(): void
    {
        $container = $this->containerWith(new DevErrorProvider());
        $renderer = $container->get(ErrorRendererInterface::class);

        self::assertInstanceOf(ErrorRendererInterface::class, $renderer);
        $response = $renderer->render(new RuntimeException('the real cause'), $this->jsonRequest());

        $data = json_decode((string) $response->getBody(), true);
        self::assertIsArray($data);
        self::assertSame(500, $data['status']);
        self::assertSame('the real cause', $data['message']);
        self::assertArrayHasKey('exception', $data);
    }
}
