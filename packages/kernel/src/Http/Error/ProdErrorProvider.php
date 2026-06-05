<?php

declare(strict_types=1);

namespace Vestige\Http\Error;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Vestige\Container\ContainerInterface;
use Vestige\Container\ServiceProviderInterface;
use Vestige\Http\Error\Format\HtmlErrorFormatRenderer;
use Vestige\Http\Error\Format\JsonProblemFormatRenderer;
use Vestige\Http\Error\Format\Responder;

final class ProdErrorProvider implements ServiceProviderInterface
{
    public function register(ContainerInterface $container): void
    {
        $responder = new Responder(
            $container->get(ResponseFactoryInterface::class),
            $container->get(StreamFactoryInterface::class),
        );

        $renderer = new ErrorRenderer(
            [new JsonProblemFormatRenderer($responder)],
            new HtmlErrorFormatRenderer($responder),
        );

        $container->bind(ErrorRendererInterface::class, $renderer);
    }
}
