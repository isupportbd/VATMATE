<?php

use Illuminate\Support\Facades\Route;
use Modules\Form46\Http\Controllers\Form46Controller;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('form46s', Form46Controller::class)->names('form46');
});
