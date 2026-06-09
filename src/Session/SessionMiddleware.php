<?php

declare(strict_types=1);

namespace Vestige\Session;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Vestige\Http\Cookie;
use Vestige\Session\Storage\SessionStorageInterface;

final readonly class SessionMiddleware implements MiddlewareInterface
{
    public const string SESSION_ATTRIBUTE = 'vestige.session';

    public function __construct(
        private SessionStorageInterface $storage,
        private SessionContext $context,
        private SessionOptions $options,
    ) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $cookieValue = $request->getCookieParams()[$this->options->cookieName] ?? null;
        $session = $this->load($cookieValue);

        $this->context->set($session);

        try {
            $response = $handler->handle($request->withAttribute(self::SESSION_ATTRIBUTE, $session));
        } finally {
            $this->context->clear();
        }

        $response = $this->persist($session, $response, $cookieValue !== null);
        $this->collectGarbage();

        return $response;
    }

    private function load(mixed $cookieValue): Session
    {
        if (is_string($cookieValue) === false) {
            return $this->freshSession();
        }

        $id = SessionId::tryFrom($cookieValue);
        if ($id === null) {
            return $this->freshSession();
        }

        $data = $this->storage->read((string) $id);
        if ($data === null) {
            return $this->freshSession();
        }

        return new Session((string) $id, $data, preExisting: true);
    }

    private function freshSession(): Session
    {
        return new Session((string) SessionId::generate(), [], preExisting: false);
    }

    private function persist(Session $session, ResponseInterface $response, bool $clientSentCookie): ResponseInterface
    {
        if ($session->isDestroyed()) {
            $this->destroyRecords($session);

            if ($clientSentCookie === false) {
                return $response;
            }

            return $this->withCookie($response, $this->expiredCookie());
        }

        $regeneratedFrom = $session->regeneratedFrom();
        if ($regeneratedFrom !== null) {
            $this->storage->destroy($regeneratedFrom);
            $this->storage->write($session->id(), $session->all(), $this->options->lifetime);

            return $this->withCookie($response, $this->liveCookie($session->id()));
        }

        if ($session->isDirty()) {
            $this->storage->write($session->id(), $session->all(), $this->options->lifetime);

            return $this->withCookie($response, $this->liveCookie($session->id()));
        }

        if ($session->isPreExisting()) {
            $this->storage->touch($session->id());

            return $this->withCookie($response, $this->liveCookie($session->id()));
        }

        return $response;
    }

    private function destroyRecords(Session $session): void
    {
        $regeneratedFrom = $session->regeneratedFrom();
        if ($regeneratedFrom !== null) {
            $this->storage->destroy($regeneratedFrom);
        }

        $this->storage->destroy($session->id());
    }

    private function withCookie(ResponseInterface $response, Cookie $cookie): ResponseInterface
    {
        return $response->withAddedHeader('Set-Cookie', (string) $cookie);
    }

    private function liveCookie(string $id): Cookie
    {
        return new Cookie(
            name: $this->options->cookieName,
            value: $id,
            maxAge: $this->options->lifetime,
            path: $this->options->cookiePath,
            domain: $this->options->cookieDomain,
            secure: $this->options->cookieSecure,
            httpOnly: $this->options->cookieHttpOnly,
            sameSite: $this->options->cookieSameSite,
        );
    }

    private function expiredCookie(): Cookie
    {
        return Cookie::expired(
            name: $this->options->cookieName,
            path: $this->options->cookiePath,
            domain: $this->options->cookieDomain,
            secure: $this->options->cookieSecure,
            httpOnly: $this->options->cookieHttpOnly,
            sameSite: $this->options->cookieSameSite,
        );
    }

    private function collectGarbage(): void
    {
        if ($this->options->gcDivisor < 1) {
            return;
        }

        if (random_int(1, $this->options->gcDivisor) !== 1) {
            return;
        }

        $this->storage->gc();
    }
}
