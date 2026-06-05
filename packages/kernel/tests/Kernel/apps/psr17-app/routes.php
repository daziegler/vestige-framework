<?php

declare(strict_types=1);

use Vestige\Http\Route;
use Vestige\Http\RouteCollection;
use Vestige\Tests\Kernel\Fixtures\FactoryProbeController;

return new RouteCollection([
    Route::get('/', FactoryProbeController::class),
]);