<?php

use Illuminate\Support\Facades\Route;
use Modules\Form43\Http\Controllers\Form43Controller;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('form43s', Form43Controller::class)->names('form43');
});
