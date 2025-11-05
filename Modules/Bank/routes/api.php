<?php

use Illuminate\Support\Facades\Route;
use Modules\Bank\Http\Controllers\BankController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('banks', BankController::class)->names('bank');
});
