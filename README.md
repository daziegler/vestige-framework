# Vestige

A tiny SOLID PHP framework. Built for PHP 8.5+.

## Status

In development. Hello-world is demoable; production-grade error handling and logging are not yet built.

## Quick start

Requires Docker and [`just`](https://github.com/casey/just).

```bash
just build                   # build the dev container
just composer install        # install dependencies
just up                      # serve on http://localhost:8000
```

## Project layout

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

## What's built

- `Vestige\Kernel` — explicit `boot()`, PSR-15 `RequestHandlerInterface`, no global state, no facades.
- `Vestige\Config\Config` — dotenv + PHP arrays, dot-notation access, `get()` strict vs `getOr()` fallback.
- `Vestige\Container\Container` — wraps `league/container` with reflection auto-wiring, generic typed `get()`.
- `Vestige\Container\ServiceProviderInterface` — Vestige-native: `register(ContainerInterface): void`.
- `Vestige\Http\Route` / `RouteCollection` — immutable VOs, validation via `Route::create()`, varargs `withMiddleware()`.
- `Vestige\Http\Router` — FastRoute adapter via `Vestige\Http\Routing\DispatcherInterface`, typed dispatch results, decoded path vars.
- `Vestige\Http\MiddlewarePipeline` — PSR-15, lazy container resolution.
- `Vestige\Http\Exceptions\*` — typed HTTP exceptions, `HttpStatus` / `HttpMethod` enums, RFC reason phrases.

## What's not built yet

- `ResponseEmitter` (currently emitted manually in `public/index.php`)
- RFC 9457 problem details (`ProblemInterface`, `Problem`)
- Error rendering pipeline (production + debug renderers)
- Logging (Monolog)

## Development

```bash
just test       # PHPUnit
just stan       # PHPStan
just cs         # php-cs-fixer (dry-run)
just cs-fix     # php-cs-fixer (apply)
just rector     # Rector (dry-run)
just rector-fix # Rector (apply)
just shell      # bash inside the dev container
```

## License

MIT
