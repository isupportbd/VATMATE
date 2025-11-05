<?php

use Illuminate\Support\Facades\Route;
use Modules\Vatfactor\Http\Controllers\VatfactorController;

// Route::middleware(['auth', 'verified'])->group(function () {
//   Route::resource('vatfactors', VatfactorController::class)->names('vatfactor');
// });

Route::middleware(['auth'])->group(function () {
  Route::get('/vatfactor-dropdown', [VatfactorController::class, 'vatfactorDropdown'])->name('vatfactor-dropdown')->middleware('role:admin'); // json response for same page and for dropdown
  Route::get('/vatfactor', [VatfactorController::class, 'index'])->name('vatfactor')->middleware('role:admin');
  Route::get('/vatfactor/{id}', [VatfactorController::class, 'show'])->name('vatfactor-single')->middleware('role:admin'); // json response for same page
  Route::put('/vatfactor/{id}', [VatfactorController::class, 'update'])->name('vatfactor-update')->middleware('role:admin');
});
