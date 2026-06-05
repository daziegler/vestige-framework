<?php

declare(strict_types=1);

namespace Vestige\Http;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Vestige\Container\ContainerInterface;

final readonly class MiddlewarePipeline implements RequestHandlerInterface
{
    /**
     * @param list<class-string<MiddlewareInterface>> $middleware
     */
    public function __construct(
        private ContainerInterface $container,
        private array $middleware,
        private RequestHandlerInterface $finalHandler,
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if ($this->middleware === []) {
            return $this->finalHandler->handle($request);
        }

        $first = $this->middleware[0];
        $rest = array_slice($this->middleware, 1);

        $middleware = $this->container->get($first);
        $next = new self($this->container, $rest, $this->finalHandler);

        return $middleware->process($request, $next);
    }
}
