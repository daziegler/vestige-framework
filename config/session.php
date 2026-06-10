<?php

declare(strict_types=1);

use Vestige\Environment;

return [
    'cookie' => [
        'secure' => Environment::fromGlobals() !== Environment::Development,
    ],
];