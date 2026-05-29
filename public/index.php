<?php

declare(strict_types=1);

use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7Server\ServerRequestCreator;
use Vestige\Environment;
use Vestige\Http\ResponseEmitter;
use Vestige\Kernel;

require __DIR__ . '/../vendor/autoload.php';

$psr17 = new Psr17Factory();
$request = new ServerRequestCreator($psr17, $psr17, $psr17, $psr17)->fromGlobals();

$kernel = new Kernel(
    basePath: dirname(__DIR__),
    env: Environment::tryFrom($_ENV['APP_ENV'] ?? '') ?? Environment::Production,
);
$kernel->boot();
$response = $kernel->handle($request);

new ResponseEmitter()->emit($response);