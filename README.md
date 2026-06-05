# Vestige

A tiny SOLID PHP framework. Built for PHP 8.5+.

## Status

In development, pre-1.0. APIs may change without notice.

## Installation

```bash
composer require vestige/kernel
```

Until the package is published on Packagist, add the repository to your `composer.json`:

```json
{
    "repositories": [
        { "type": "vcs", "url": "https://github.com/daziegler/vestige" }
    ],
    "require": {
        "vestige/kernel": "dev-main"
    }
}
```

## Usage

A minimal app needs a front controller, a routes file, and an app config.

`public/index.php`:

```php
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
```

`routes.php`:

```php
<?php

declare(strict_types=1);

use App\Http\HelloController;
use Vestige\Http\Route;
use Vestige\Http\RouteCollection;

return new RouteCollection([
    Route::get('/', HelloController::class),
]);
```

A controller:

```php
<?php

declare(strict_types=1);

namespace App\Http;

use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Vestige\Http\ControllerInterface;

final class HelloController implements ControllerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return new Response(200, ['Content-Type' => 'text/plain'], 'Hello, Vestige!');
    }
}
```

`config/app.php`:

```php
<?php

declare(strict_types=1);

use Psr\Log\LoggerInterface;
use Vestige\Environment;
use Vestige\Http\Error\DevErrorProvider;
use Vestige\Http\Error\ErrorHandlerMiddleware;
use Vestige\Http\Error\ProdErrorProvider;
use Vestige\Log\LoggingProvider;

$env = Environment::tryFrom($_ENV['APP_ENV'] ?? '') ?? Environment::Production;

return [
    'providers' => [
        LoggingProvider::class,
        match ($env) {
            Environment::Development => DevErrorProvider::class,
            default => ProdErrorProvider::class,
        },
    ],
    'eager' => [
        LoggerInterface::class,
    ],
    'middleware' => [
        ErrorHandlerMiddleware::class,
    ],
];
```

## Features

- `Vestige\Kernel` — explicit `boot()`, PSR-15 `RequestHandlerInterface`, no global state, no facades.
- `Vestige\Config\Config` — dotenv + PHP arrays, dot-notation access, `get()` strict vs `getOr()` fallback.
- `Vestige\Container\Container` — wraps `league/container` with reflection auto-wiring, generic typed `get()`.
- `Vestige\Container\ServiceProviderInterface` — Vestige-native: `register(ContainerInterface): void`.
- `Vestige\Http\Route` / `RouteCollection` — immutable VOs, validation via `Route::create()`, varargs `withMiddleware()`.
- `Vestige\Http\Router` — FastRoute adapter via `Vestige\Http\Routing\DispatcherInterface`, typed dispatch results, decoded path vars.
- `Vestige\Http\MiddlewarePipeline` — PSR-15, lazy container resolution.
- `Vestige\Http\Exceptions\*` — typed HTTP exceptions, `HttpStatus` / `HttpMethod` enums, RFC reason phrases.
- `Vestige\Http\ResponseEmitter` — PSR-7 response emission, chunked body output, throws if headers already sent.
- `Vestige\Http\Problem\Problem` — RFC 9457 problem details, built from HTTP exceptions or status codes.
- `Vestige\Http\Error\*` — error rendering pipeline: `ErrorHandlerMiddleware`, production + debug renderers, HTML + JSON problem formats.
- `Vestige\Log\LoggingProvider` — PSR-3 `LoggerInterface` via Monolog, stderr stream handler.

## Development

This repo is the `vestige/kernel` package; a dev-only demo app rides along (excluded from dist via `.gitattributes`).

```
.
├── src/                     # the framework itself
├── tests/                   # kernel tests
├── public/index.php         # demo app: front controller
├── routes.php               # demo app: returns a RouteCollection
├── config/                  # demo app: config (PHP files returning arrays)
└── app/                     # demo app: controllers, providers
```

Requires Docker and [`just`](https://github.com/casey/just).

```bash
just build      # build the dev container
just composer install
just up         # serve on http://localhost:8000
just shell      # bash inside the dev container
just test       # PHPUnit
just stan       # PHPStan
just cs         # php-cs-fixer (dry-run)
just cs-fix     # php-cs-fixer (apply)
just rector     # Rector (dry-run)
just rector-fix # Rector (apply)
```

## License

MIT