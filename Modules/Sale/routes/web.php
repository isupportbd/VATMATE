<?php

use Illuminate\Support\Facades\Route;
use Modules\Sale\Http\Controllers\SaleController;

// Route::middleware(['auth', 'verified'])->group(function () {
//   Route::resource('sales', SaleController::class)->names('sale');
// });

Route::middleware(['auth'])->group(function () {
  Route::get('/last-invoice', [SaleController::class, 'lastInvoice'])->name('last-invoice')->middleware('role:admin,manager');
  Route::get('/sale-limit', [SaleController::class, 'saleLimit'])->name('sale-limit')->middleware('role:admin,manager');
  Route::get('/sale', [SaleController::class, 'index'])->name('sale')->middleware('role:admin,manager');
  Route::post('/sale', [SaleController::class, 'store'])->name('sale-create')->middleware('role:admin,manager');
  Route::get('/sale/{id}', [SaleController::class, 'show'])->name('sale-single')->middleware('role:admin,manager');
  Route::put('/sale/{id}', [SaleController::class, 'update'])->name('sale-update')->middleware('role:admin,manager');
  Route::put('/sale-customer-update/{id}', [SaleController::class, 'updateCustomer'])->name('sale-customer-update')->middleware('role:admin,manager');
  Route::delete('/sale/{id}', [SaleController::class, 'destroy'])->name('sale-delete')->middleware('role:admin,manager');
});
