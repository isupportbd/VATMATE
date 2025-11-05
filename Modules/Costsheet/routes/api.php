<?php

use Illuminate\Support\Facades\Route;
use Modules\Costsheet\Http\Controllers\CostsheetController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('costsheets', CostsheetController::class)->names('costsheet');
});
