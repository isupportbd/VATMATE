<?php

use Illuminate\Support\Facades\Route;
use Modules\Form621\Http\Controllers\Form621Controller;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('form621s', Form621Controller::class)->names('form621');
});
