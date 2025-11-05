<?php

use Illuminate\Support\Facades\Route;
use Modules\Creditnote\Http\Controllers\CreditnoteController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('creditnotes', CreditnoteController::class)->names('creditnote');
});
