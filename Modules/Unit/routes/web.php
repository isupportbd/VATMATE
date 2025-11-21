<?php

use Illuminate\Support\Facades\Route;
use Modules\Unit\Http\Controllers\UnitController;

// Route::middleware(['auth', 'verified'])->group(function () {
//   Route::resource('units', UnitController::class)->names('unit');
// });

Route::middleware(['auth'])->group(function () {
  Route::get('/unit-dropdown', [UnitController::class, 'unitDropdown'])->name('unit-dropdown')->middleware('role:admin,manager'); // json response for same page and for dropdown
  Route::get('/unit', [UnitController::class, 'index'])->name('unit')->middleware('role:admin');
  Route::post('/unit', [UnitController::class, 'store'])->name('unit-create')->middleware('role:admin');
  Route::get('/unit/{id}', [UnitController::class, 'show'])->name('unit-single')->middleware('role:admin'); // json response for same page
  Route::put('/unit/{id}', [UnitController::class, 'update'])->name('unit-update')->middleware('role:admin');
  Route::delete('/unit/{id}', [UnitController::class, 'destroy'])->name('unit-delete')->middleware('role:admin');
});
