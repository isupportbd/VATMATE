<?php

use Illuminate\Support\Facades\Route;
use Modules\Adjustment\Http\Controllers\AdjustmentController;

// Route::middleware(['auth', 'verified'])->group(function () {
//   Route::resource('adjustments', AdjustmentController::class)->names('adjustment');
// });

Route::middleware(['auth'])->group(function () {
  Route::get('/adjustment-dropdown', [AdjustmentController::class, 'adjustmentDropdown'])->name('adjustment-dropdown')->middleware('role:admin,manager'); // json response for same page and for dropdown
  Route::get('/adjustment', [AdjustmentController::class, 'index'])->name('adjustment')->middleware('role:admin,manager');
  Route::post('/adjustment', [AdjustmentController::class, 'store'])->name('adjustment-create')->middleware('role:admin,manager');
  Route::get('/adjustment/{id}', [AdjustmentController::class, 'show'])->name('adjustment-single')->middleware('role:admin,manager'); // json response for same page
  Route::put('/adjustment/{id}', [AdjustmentController::class, 'update'])->name('adjustment-update')->middleware('role:admin,manager');
  Route::delete('/adjustment/{id}', [AdjustmentController::class, 'destroy'])->name('adjustment-delete')->middleware('role:admin,manager');
});
