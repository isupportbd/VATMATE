<?php

use Illuminate\Support\Facades\Route;
use Modules\Debitnote\Http\Controllers\DebitnoteController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('debitnotes', DebitnoteController::class)->names('debitnote');
});
