<?php

use Illuminate\Support\Facades\Route;
use Modules\Form44\Http\Controllers\Form44Controller;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('form44s', Form44Controller::class)->names('form44');
});
