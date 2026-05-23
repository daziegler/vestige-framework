<?php

declare(strict_types=1);

use App\Http\HelloController;
use Vestige\Http\Route;
use Vestige\Http\RouteCollection;

return new RouteCollection([
    Route::get('/', HelloController::class),
]);
