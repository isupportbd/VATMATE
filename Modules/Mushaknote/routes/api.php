<?php

use Illuminate\Support\Facades\Route;
use Modules\Mushaknote\Http\Controllers\MushaknoteController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('mushaknotes', MushaknoteController::class)->names('mushaknote');
});
