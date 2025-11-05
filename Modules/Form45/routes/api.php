<?php

use Illuminate\Support\Facades\Route;
use Modules\Form45\Http\Controllers\Form45Controller;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('form45s', Form45Controller::class)->names('form45');
});
