<?php

use Illuminate\Support\Facades\Route;
use Modules\Sale\Http\Controllers\SaleController;

// Route::middleware(['auth', 'verified'])->group(function () {
//   Route::resource('sales', SaleController::class)->names('sale');
// });

Route::middleware(['auth'])->group(function () {
  Route::get('/sale', [SaleController::class, 'index'])->name('sale')->middleware('role:admin,manager');
  Route::post('/sale', [SaleController::class, 'store'])->name('sale-create')->middleware('role:admin,manager');
  Route::get('/sale/distinct/client', [SaleController::class, 'indexDistinctClient'])->name('sale-distinct-client')->middleware('role:admin,manager');
  Route::get('/sale/distinct/item', [SaleController::class, 'indexDistinctItem'])->name('sale-distinct-item')->middleware('role:admin,manager');
  Route::get('/sale/client/single/alldata/{clientbin_id}', [SaleController::class, 'indexSingleClientAllData'])->name('sale-client-single-alldata')->middleware('role:admin,manager');
  Route::get('/sale/{id}', [SaleController::class, 'show'])->name('sale-single')->middleware('role:admin,manager');
  Route::put('/sale/{id}', [SaleController::class, 'update'])->name('sale-update')->middleware('role:admin,manager');
  Route::delete('/sale/{id}', [SaleController::class, 'destroy'])->name('sale-delete')->middleware('role:admin,manager');
  Route::put('/sale/customer/edit/{id}', [SaleController::class, 'updateCustomer'])->name('sale-customer-update')->middleware('role:admin,manager');
  Route::get('/sale/distinct/client/import/report63', [SaleController::class, 'indexDistinctClientImport63'])->name('sale-distinct-client-import-report63')->middleware('role:admin,manager');
});
