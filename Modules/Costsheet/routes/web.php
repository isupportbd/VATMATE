<?php

use Illuminate\Support\Facades\Route;
use Modules\Costsheet\Http\Controllers\CostsheetController;

// Route::middleware(['auth', 'verified'])->group(function () {
//   Route::resource('costsheets', CostsheetController::class)->names('costsheet');
// });

Route::middleware(['auth'])->group(function () {
  // action route
  Route::get('/costsheet-dropdown', [CostsheetController::class, 'costsheetDropdown'])->name('costsheet-dropdown')->middleware('role:admin,manager');
  Route::get('/costsheet', [CostsheetController::class, 'index'])->name('costsheet')->middleware('role:admin');
  Route::post('/costsheet', [CostsheetController::class, 'store'])->name('costsheet-create')->middleware('role:admin');
  Route::get('/costsheet/{id}', [CostsheetController::class, 'show'])->name('costsheet-single')->middleware('role:admin');
  Route::put('/costsheet/{id}', [CostsheetController::class, 'update'])->name('costsheet-update')->middleware('role:admin');
  Route::delete('/costsheet/{id}', [CostsheetController::class, 'destroy'])->name('costsheet-delete')->middleware('role:admin');
});
