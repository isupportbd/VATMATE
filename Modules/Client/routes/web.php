<?php

use Illuminate\Support\Facades\Route;
use Modules\Client\Http\Controllers\ClientController;

// Route::middleware(['auth', 'verified'])->group(function () {
//   Route::resource('clients', ClientController::class)->names('client');
// });

Route::middleware(['auth'])->group(function () {
  Route::get('/client-droppdown', [ClientController::class, 'clientDropdown'])->name('client-dropdown')->middleware('role:admin,manager'); // json response for same page and for dropdown
  Route::get('/client', [ClientController::class, 'index'])->name('client')->middleware('role:admin,manager');
  Route::post('/client', [ClientController::class, 'store'])->name('client-create')->middleware('role:admin,manager');
  Route::get('/client/{id}', [ClientController::class, 'show'])->name('client-single')->middleware('role:admin,manager'); // json response for same page
  Route::put('/client/{id}', [ClientController::class, 'update'])->name('client-update')->middleware('role:admin,manager');
  Route::delete('/client/{id}', [ClientController::class, 'destroy'])->name('client-delete')->middleware('role:admin');
  Route::put('/client/status/{id}', [ClientController::class, 'statusUpdate'])->name('client-status')->middleware('role:admin');
});
