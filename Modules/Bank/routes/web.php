<?php

use Illuminate\Support\Facades\Route;
use Modules\Bank\Http\Controllers\BankController;

// Route::middleware(['auth', 'verified'])->group(function () {
//   Route::resource('banks', BankController::class)->names('bank');
// });

Route::middleware(['auth'])->group(function () {
  Route::get('/bank-dropdown', [BankController::class, 'bankDropdown'])->name('bank-dropdown')->middleware('role:admin'); // json response for same page and for dropdown
  Route::get('/bank', [BankController::class, 'index'])->name('bank')->middleware('role:admin,manager');
  Route::post('/bank', [BankController::class, 'store'])->name('bank-create')->middleware('role:admin');
  Route::get('/bank/{id}', [BankController::class, 'show'])->name('bank-single')->middleware('role:admin,manager'); // json response for same page
  Route::put('/bank/{id}', [BankController::class, 'update'])->name('bank-update')->middleware('role:admin');
  Route::delete('/bank/{id}', [BankController::class, 'destroy'])->name('bank-delete')->middleware('role:admin');
});
