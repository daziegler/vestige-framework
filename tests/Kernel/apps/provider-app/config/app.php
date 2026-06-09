<?php

declare(strict_types=1);

use Vestige\Tests\Kernel\Fixtures\EagerProbe;
use Vestige\Tests\Kernel\Fixtures\GreetingProvider;

return [
    'providers' => [
        GreetingProvider::class,
    ],
    'eager' => [
        EagerProbe::class,
    ],
];
