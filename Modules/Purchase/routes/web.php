<?php

use Illuminate\Support\Facades\Route;
use Modules\Purchase\Http\Controllers\PurchaseController;

// Route::middleware(['auth', 'verified'])->group(function () {
//   Route::resource('purchases', PurchaseController::class)->names('purchase');
// });

Route::middleware(['auth'])->group(function () {
  Route::get('/purchase-lc-search', [PurchaseController::class, 'purchaseLcSearch'])->name('purchase-lc-search')->middleware('role:admin,manager');
  Route::get('/purchase', [PurchaseController::class, 'index'])->name('purchase')->middleware('role:admin,manager');
  Route::post('/purchase', [PurchaseController::class, 'store'])->name('purchase-create')->middleware('role:admin,manager');
  Route::get('/purchase/{id}', [PurchaseController::class, 'show'])->name('purchase-single')->middleware('role:admin,manager');
  Route::put('/purchase/{id}', [PurchaseController::class, 'update'])->name('purchase-update')->middleware('role:admin,manager');
  Route::put('/purchase-exporter-update/{id}', [PurchaseController::class, 'updateExporter'])->name('purchase-exporter-update')->middleware('role:admin,manager');
  Route::delete('/purchase/{id}', [PurchaseController::class, 'destroy'])->name('purchase-delete')->middleware('role:admin,manager');
});
