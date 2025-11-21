<?php

use Illuminate\Support\Facades\Route;
use Modules\EconomicArea\Http\Controllers\EconomicAreaController;

// Route::middleware(['auth', 'verified'])->group(function () {
//   Route::resource('economicareas', EconomicAreaController::class)->names('economicarea');
// });

Route::middleware(['auth'])->group(function () {
  Route::post('/economicarea', [EconomicareaController::class, 'store'])->name('economicarea-create')->middleware('role:admin,manager');
  Route::get('/economicarea', [EconomicareaController::class, 'index'])->name('economicarea')->middleware('role:admin,manager');
  Route::get('/client-item-dropdown', [EconomicareaController::class, 'clientItemDropdown'])->name('client-item-dropdown')->middleware('role:admin,manager');
  Route::delete('/economicarea/{client_id}/{item_id}', [EconomicareaController::class, 'destroy'])->name('economicarea-delete')->middleware('role:admin,manager');
});
