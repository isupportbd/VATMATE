<?php

use Illuminate\Support\Facades\Route;
use Modules\Division\Http\Controllers\DivisionController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('divisions', DivisionController::class)->names('division');
});
