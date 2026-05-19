<?php

declare(strict_types=1);

namespace Vestige\Container;

interface DefinitionInterface
{
    public function shared(): self;
}
