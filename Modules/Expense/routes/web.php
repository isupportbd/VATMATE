<?php

use Illuminate\Support\Facades\Route;
use Modules\Expense\Http\Controllers\ExpenseController;

// Route::middleware(['auth', 'verified'])->group(function () {
//   Route::resource('expenses', ExpenseController::class)->names('expense');
// });

Route::middleware(['auth'])->group(function () {
  Route::get('/expense', [ExpenseController::class, 'index'])->name('expense-dropdown')->middleware('role:admin,manager');
  Route::get('/expense/{id}', [ExpenseController::class, 'show'])->name('expense-single')->middleware('role:admin,manager');
});
