<?php

declare(strict_types=1);

namespace Vestige;

use Dotenv\Dotenv;
use League\Container\Container as LeagueContainer;
use League\Container\ReflectionContainer;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Vestige\Config\Config;
use Vestige\Container\Container;
use Vestige\Container\ServiceProviderInterface;
use Vestige\Exceptions\KernelNotBootedException;
use Vestige\Exceptions\RoutesFileException;
use Vestige\Http\MiddlewarePipeline;
use Vestige\Http\RouteCollection;
use Vestige\Http\Router;
use Vestige\Http\Routing\FastRouteDispatcher;

final class Kernel implements RequestHandlerInterface
{
    private Container $container;
    private Config $config;
    private bool $booted = false;

    public function __construct(
        private readonly string $basePath,
        private readonly Environment $env = Environment::Production,
    ) {}

    public function boot(): void
    {
        if ($this->booted) {
            return;
        }

        Dotenv::createImmutable($this->basePath)->safeLoad();
        $this->config = Config::fromDirectory($this->basePath . '/config');

        $this->initContainer();
        $this->initRouter();
        $this->registerProviders();
        $this->loadServices();

        $this->booted = true;
    }

    private function initContainer(): void
    {
        $league = new LeagueContainer();
        $league->delegate(new ReflectionContainer());

        $this->container = new Container($league);
        $this->container->bind(Config::class, $this->config);
        $this->container->bind(Environment::class, $this->env);
    }

    private function initRouter(): void
    {
        $routesFile = $this->basePath . '/routes.php';
        if (is_file($routesFile) === false) {
            throw RoutesFileException::missing($routesFile);
        }

        $routes = require $routesFile;
        if (! $routes instanceof RouteCollection) {
            throw RoutesFileException::invalidReturn($routesFile, $routes);
        }

        $this->container->bind(
            Router::class,
            new Router($this->container, new FastRouteDispatcher($routes)),
        );
    }

    private function registerProviders(): void
    {
        /** @var class-string<ServiceProviderInterface>[] $providers */
        $providers = $this->config->getOr('app.providers', []);
        foreach ($providers as $class) {
            $provider = new $class();
            $provider->register($this->container);
        }
    }

    private function loadServices(): void
    {
        /** @var string[]|class-string[] $eagerLoadedServices */
        $eagerLoadedServices = $this->config->getOr('app.eager', []);
        foreach ($eagerLoadedServices as $service) {
            $this->container->get($service);
        }
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if ($this->booted === false) {
            throw KernelNotBootedException::create();
        }

        /** @var list<class-string<MiddlewareInterface>> $middleware */
        $middleware = $this->config->getOr('app.middleware', []);

        $pipeline = new MiddlewarePipeline(
            $this->container,
            $middleware,
            $this->container->get(Router::class),
        );

        return $pipeline->handle($request);
    }
}
