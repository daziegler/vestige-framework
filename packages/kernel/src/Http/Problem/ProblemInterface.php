<?php

declare(strict_types=1);

namespace Vestige\Http\Problem;

use Vestige\Http\HttpStatus;

interface ProblemInterface
{
    public function getType(): string;

    public function getTitle(): string;

    public function getStatus(): HttpStatus;

    public function getDetail(): ?string;

    public function getInstance(): ?string;

    /** @return array<string, mixed> */
    public function getExtensions(): array;

    /** @return array<string, mixed> */
    public function toArray(): array;
}