<?php

use Illuminate\Support\Facades\Route;
use Modules\Rate\Http\Controllers\RateController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('rates', RateController::class)->names('rate');
});
