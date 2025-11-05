<?php

use Illuminate\Support\Facades\Route;
use Modules\Treasury\Http\Controllers\TreasuryController;

// Route::middleware(['auth', 'verified'])->group(function () {
//   Route::resource('treasuries', TreasuryController::class)->names('treasury');
// });

Route::middleware(['auth'])->group(function () {
  Route::get('/treasury-dropdown', [TreasuryController::class, 'treasuryDropdown'])->name('treasury-dropdown')->middleware('role:admin,manager'); // json response for same page and for dropdown
  Route::get('/treasury', [TreasuryController::class, 'index'])->name('treasury')->middleware('role:admin,manager');
  Route::post('/treasury', [TreasuryController::class, 'store'])->name('treasury-create')->middleware('role:admin,manager');
  Route::get('/treasury/{id}', [TreasuryController::class, 'show'])->name('treasury-single')->middleware('role:admin,manager'); // json response for same page
  Route::put('/treasury/{id}', [TreasuryController::class, 'update'])->name('treasury-update')->middleware('role:admin,manager');
  Route::delete('/treasury/{id}', [TreasuryController::class, 'destroy'])->name('treasury-delete')->middleware('role:admin,manager');
});
