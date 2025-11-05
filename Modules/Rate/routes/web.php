<?php

use Illuminate\Support\Facades\Route;
use Modules\Rate\Http\Controllers\RateController;

// Route::middleware(['auth', 'verified'])->group(function () {
//   Route::resource('rates', RateController::class)->names('rate');
// });

Route::middleware(['auth'])->group(function () {
  Route::get('/rate', [RateController::class, 'index'])->name('rate')->middleware('role:admin,manager');
  Route::post('/rate', [RateController::class, 'store'])->name('rate-create')->middleware('role:admin,manager');
  Route::get('/rate/{id}', [RateController::class, 'show'])->name('rate-single')->middleware('role:admin,manager'); // json response for same page
  Route::put('/rate/{id}', [RateController::class, 'update'])->name('rate-update')->middleware('role:admin,manager');
  Route::delete('/rate/{id}', [RateController::class, 'destroy'])->name('rate-delete')->middleware('role:admin,manager');
});
