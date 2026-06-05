<?php

declare(strict_types=1);

namespace Vestige\Http;

use LogicException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Vestige\Container\ContainerInterface;
use Vestige\Http\Exceptions\MethodNotAllowedException;
use Vestige\Http\Exceptions\NotFoundException;
use Vestige\Http\Routing\DispatcherInterface;
use Vestige\Http\Routing\Found;
use Vestige\Http\Routing\MethodNotAllowed;
use Vestige\Http\Routing\NotFound;

final readonly class Router implements RequestHandlerInterface
{
    public function __construct(
        private ContainerInterface $container,
        private DispatcherInterface $dispatcher,
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $result = $this->dispatcher->dispatch(
            $request->getMethod(),
            $request->getUri()->getPath(),
        );

        return match (true) {
            $result instanceof Found => $this->dispatchFound($request, $result),
            $result instanceof MethodNotAllowed => throw new MethodNotAllowedException($result->allowedMethods),
            $result instanceof NotFound => throw new NotFoundException(),
            default => throw new LogicException('Unhandled dispatch result type: ' . $result::class),
        };
    }

    private function dispatchFound(ServerRequestInterface $request, Found $found): ResponseInterface
    {
        foreach ($found->vars as $key => $value) {
            $request = $request->withAttribute($key, $value);
        }

        $controller = $this->container->get($found->route->controller);

        $pipeline = new MiddlewarePipeline(
            $this->container,
            $found->route->middleware,
            $controller,
        );

        return $pipeline->handle($request);
    }
}
