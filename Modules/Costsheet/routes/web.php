<?php

use Illuminate\Support\Facades\Route;
use Modules\Costsheet\Http\Controllers\CostsheetController;

// Route::middleware(['auth', 'verified'])->group(function () {
//   Route::resource('costsheets', CostsheetController::class)->names('costsheet');
// });

Route::middleware(['auth'])->group(function () {
  // action route
  Route::get('/costsheet', [CostsheetController::class, 'index'])->name('costsheet-dropdown')->middleware('role:admin,manager');
  Route::get('/costsheet/{id}', [CostsheetController::class, 'show'])->name('costsheet-single')->middleware('role:admin,manager');
});
