<?php

declare(strict_types=1);

namespace Vestige\Http\Problem;

use Psr\Http\Message\ServerRequestInterface;
use Vestige\Http\Exceptions\HttpExceptionInterface;
use Vestige\Http\Exceptions\PublicMessageInterface;
use Vestige\Http\HttpStatus;

final readonly class Problem
{
    /** @param array<string, mixed> $extensions */
    public function __construct(
        private HttpStatus $status,
        private string $title,
        private string $type = Rfc9457::DEFAULT_TYPE,
        private ?string $detail = null,
        private ?string $instance = null,
        private array $extensions = [],
    ) {}

    public static function forStatus(HttpStatus $status): self
    {
        return new self(status: $status, title: $status->reasonPhrase());
    }

    public static function fromHttpException(HttpExceptionInterface $exception, ServerRequestInterface $request): self
    {
        $status = $exception->getStatusCode();
        $path = $request->getUri()->getPath();

        return new self(
            status: $status,
            title: $status->reasonPhrase(),
            detail: $exception instanceof PublicMessageInterface ? $exception->getPublicMessage() : null,
            instance: $path === '' ? null : $path,
        );
    }

    public function withExtension(string $key, mixed $value): self
    {
        return clone($this, [
            'extensions' => [...$this->extensions, $key => $value],
        ]);
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getStatus(): HttpStatus
    {
        return $this->status;
    }

    public function getDetail(): ?string
    {
        return $this->detail;
    }

    public function getInstance(): ?string
    {
        return $this->instance;
    }

    /** @return array<string, mixed> */
    public function getExtensions(): array
    {
        return $this->extensions;
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        $base = [
            'type' => $this->type,
            'title' => $this->title,
            'status' => $this->status->value,
        ];

        if ($this->detail !== null) {
            $base['detail'] = $this->detail;
        }

        if ($this->instance !== null) {
            $base['instance'] = $this->instance;
        }

        return [...$base, ...$this->extensions];
    }
}
