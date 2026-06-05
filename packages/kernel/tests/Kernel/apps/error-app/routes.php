<?php

declare(strict_types=1);

use Vestige\Http\Route;
use Vestige\Http\RouteCollection;
use Vestige\Tests\Http\Fixtures\HelloController;

return new RouteCollection([
    Route::get('/', HelloController::class),
]);