<?php

use Illuminate\Support\Facades\Route;
use Modules\Addition\Http\Controllers\AdditionController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('additions', AdditionController::class)->names('addition');
});
