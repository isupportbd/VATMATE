<?php

use Illuminate\Support\Facades\Route;
use Modules\Stationcode\Http\Controllers\StationcodeController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('stationcodes', StationcodeController::class)->names('stationcode');
});
