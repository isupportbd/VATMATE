<?php

use Illuminate\Support\Facades\Route;
use Modules\Adjustment\Http\Controllers\AdjustmentController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('adjustments', AdjustmentController::class)->names('adjustment');
});
