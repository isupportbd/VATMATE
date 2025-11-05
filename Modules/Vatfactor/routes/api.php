<?php

use Illuminate\Support\Facades\Route;
use Modules\Vatfactor\Http\Controllers\VatfactorController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('vatfactors', VatfactorController::class)->names('vatfactor');
});
