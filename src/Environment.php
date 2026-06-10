<?php

declare(strict_types=1);

namespace Vestige;

enum Environment: string
{
    case Development = 'development';
    case Production = 'production';
    case Testing = 'testing';

    public static function fromGlobals(): self
    {
        $value = $_ENV['APP_ENV'] ?? '';
        if (is_string($value) === false) {
            return self::Production;
        }

        return self::tryFrom($value) ?? self::Production;
    }
}
