<?php

declare(strict_types=1);

namespace Vestige\Tests\Kernel\Fixtures;

final readonly class Greeting
{
    public function __construct(public string $message) {}
}
