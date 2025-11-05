<?php

use Illuminate\Support\Facades\Route;
use Modules\Division\Http\Controllers\DivisionController;

// Route::middleware(['auth', 'verified'])->group(function () {
//   Route::resource('divisions', DivisionController::class)->names('division');
// });

Route::middleware(['auth'])->group(function () {
  Route::get('/division-dropdown', [DivisionController::class, 'divisionDropdown'])->name('division-dropdown')->middleware('role:admin,manager'); // json response for same page and for dropdown
  Route::get('/division', [DivisionController::class, 'index'])->name('division')->middleware('role:admin,manager');
  Route::post('/division', [DivisionController::class, 'store'])->name('division-create')->middleware('role:admin');
  Route::get('/division/{id}', [DivisionController::class, 'show'])->name('division-single')->middleware('role:admin,manager'); // json response for same page
  Route::put('/division/{id}', [DivisionController::class, 'update'])->name('division-update')->middleware('role:admin');
  Route::delete('/division/{id}', [DivisionController::class, 'destroy'])->name('division-delete')->middleware('role:admin');
});
