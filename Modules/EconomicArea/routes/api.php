<?php

use Illuminate\Support\Facades\Route;
use Modules\EconomicArea\Http\Controllers\EconomicAreaController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('economicareas', EconomicAreaController::class)->names('economicarea');
});
