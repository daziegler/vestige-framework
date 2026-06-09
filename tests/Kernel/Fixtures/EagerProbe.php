<?php

declare(strict_types=1);

namespace Vestige\Tests\Kernel\Fixtures;

final class EagerProbe
{
    public static bool $constructed = false;

    public function __construct()
    {
        self::$constructed = true;
    }
}
