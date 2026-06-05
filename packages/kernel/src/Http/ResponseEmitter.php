<?php

declare(strict_types=1);

namespace Vestige\Http;

use Psr\Http\Message\ResponseInterface;
use Vestige\Http\Exceptions\HeadersAlreadySentException;

final class ResponseEmitter
{
    private const int CHUNK_SIZE = 8192;

    public function emit(ResponseInterface $response): void
    {
        if (headers_sent()) {
            throw HeadersAlreadySentException::create();
        }

        $this->emitHeaders($response);
        $this->emitStatusLine($response);
        $this->emitBody($response);
    }

    private function emitHeaders(ResponseInterface $response): void
    {
        foreach ($response->getHeaders() as $name => $values) {
            $replace = strcasecmp($name, 'Set-Cookie') !== 0;

            foreach ($values as $value) {
                header(sprintf('%s: %s', $name, $value), $replace);
                $replace = false;
            }
        }
    }

    private function emitStatusLine(ResponseInterface $response): void
    {
        header(
            sprintf(
                'HTTP/%s %d %s',
                $response->getProtocolVersion(),
                $response->getStatusCode(),
                $response->getReasonPhrase(),
            ),
            true,
            $response->getStatusCode(),
        );
    }

    private function emitBody(ResponseInterface $response): void
    {
        $body = $response->getBody();
        if ($body->isSeekable()) {
            $body->rewind();
        }

        while (! $body->eof()) {
            echo $body->read(self::CHUNK_SIZE);
        }
    }
}