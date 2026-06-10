<?php

declare(strict_types=1);

namespace Vestige\Http;

enum SameSite: string
{
    case Lax = 'Lax';
    case Strict = 'Strict';
    case None = 'None';
}
