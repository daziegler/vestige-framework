<?php

declare(strict_types=1);

namespace Vestige\Http\Exceptions;

interface PublicMessageInterface
{
    public function getPublicMessage(): string;
}
