<?php

use Illuminate\Support\Facades\Route;
use Modules\Branch\Http\Controllers\BranchController;

// Route::middleware(['auth', 'verified'])->group(function () {
//   Route::resource('branches', BranchController::class)->names('branch');
// });

Route::middleware(['auth'])->group(function () {
  Route::get('/branch-dropdown', [BranchController::class, 'branchDropdown'])->name('branch-dropdown')->middleware('role:admin,manager'); // json response for same page and for dropdown
  Route::get('/branch', [BranchController::class, 'index'])->name('branch')->middleware('role:admin');
  Route::post('/branch', [BranchController::class, 'store'])->name('branch-create')->middleware('role:admin');
  Route::get('/branch/{id}', [BranchController::class, 'show'])->name('branch-single')->middleware('role:admin'); // json response for same page
  Route::put('/branch/{id}', [BranchController::class, 'update'])->name('branch-update')->middleware('role:admin');
  Route::delete('/branch/{id}', [BranchController::class, 'destroy'])->name('branch-delete')->middleware('role:admin');
});
