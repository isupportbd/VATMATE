<?php

use Illuminate\Support\Facades\Route;
use Modules\Addition\Http\Controllers\AdditionController;

// Route::middleware(['auth', 'verified'])->group(function () {
//   Route::resource('additions', AdditionController::class)->names('addition');
// });

Route::middleware(['auth'])->group(function () {
  Route::post('/addition', [AdditionController::class, 'store'])->name('addition-create')->middleware('role:admin,manager');
  Route::get('/addition/index/{id}', [AdditionController::class, 'index'])->name('addition')->middleware('role:admin,manager');
  Route::get('/addition/{id}', [AdditionController::class, 'show'])->name('addition-single')->middleware('role:admin,manager'); // json response for same page
  Route::put('/addition/{id}', [AdditionController::class, 'update'])->name('addition-update')->middleware('role:admin,manager');
  Route::delete('/addition/{id}', [AdditionController::class, 'destroy'])->name('addition-delete')->middleware('role:admin,manager');
});
