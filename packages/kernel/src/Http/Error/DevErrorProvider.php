<?php

declare(strict_types=1);

namespace Vestige\Http\Error;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Vestige\Container\ContainerInterface;
use Vestige\Container\ServiceProviderInterface;
use Vestige\Http\Error\Format\DebugHtmlErrorFormatRenderer;
use Vestige\Http\Error\Format\DebugJsonProblemFormatRenderer;
use Vestige\Http\Error\Format\JsonProblemFormatRenderer;
use Vestige\Http\Error\Format\Responder;

final class DevErrorProvider implements ServiceProviderInterface
{
    public function register(ContainerInterface $container): void
    {
        $responder = new Responder(
            $container->get(ResponseFactoryInterface::class),
            $container->get(StreamFactoryInterface::class),
        );

        $renderer = new ErrorRenderer(
            [new DebugJsonProblemFormatRenderer(new JsonProblemFormatRenderer($responder))],
            new DebugHtmlErrorFormatRenderer($responder),
        );

        $container->bind(ErrorRendererInterface::class, $renderer);
    }
}
