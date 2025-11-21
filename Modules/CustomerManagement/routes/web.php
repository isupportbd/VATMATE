<?php

use Illuminate\Support\Facades\Route;
use Modules\CustomerManagement\Http\Controllers\CustomerManagementController;

// Route::middleware(['auth', 'verified'])->group(function () {
//   Route::resource('customermanagements', CustomerManagementController::class)->names('customermanagement');
// });

Route::middleware(['auth'])->group(function () {
  Route::get('/customer-dropdown', [CustomerManagementController::class, 'customerDropdown'])->name('customer-dropdown')->middleware('role:admin,manager'); // json response for same page and for dropdown
  Route::get('/customer', [CustomerManagementController::class, 'index'])->name('customer')->middleware('role:admin,manager');
  Route::post('/customer', [CustomerManagementController::class, 'store'])->name('customer-create')->middleware('role:admin,manager');
  Route::get('/customer/{id}', [CustomerManagementController::class, 'show'])->name('customer-single')->middleware('role:admin,manager'); // json response for same page
  Route::put('/customer/{id}', [CustomerManagementController::class, 'update'])->name('customer-update')->middleware('role:admin,manager');
  Route::delete('/customer/{id}', [CustomerManagementController::class, 'destroy'])->name('customer-delete')->middleware('role:admin,manager');
});
