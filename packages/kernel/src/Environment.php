<?php

declare(strict_types=1);

namespace Vestige;

enum Environment: string
{
    case Development = 'development';
    case Production = 'production';
    case Testing = 'testing';
}
