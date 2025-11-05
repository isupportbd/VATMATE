<?php

use Illuminate\Support\Facades\Route;
use Modules\Stationcode\Http\Controllers\StationcodeController;

// Route::middleware(['auth', 'verified'])->group(function () {
//   Route::resource('stationcodes', StationcodeController::class)->names('stationcode');
// });

Route::middleware(['auth'])->group(function () {
  Route::get('/stationcode-dropdown', [StationcodeController::class, 'stationcodeDropdown'])->name('stationcode-dropdown')->middleware('role:admin,manager'); // json response for same page and for dropdown
  Route::get('/stationcode', [StationcodeController::class, 'index'])->name('stationcode')->middleware('role:admin,manager');
  Route::post('/stationcode', [StationcodeController::class, 'store'])->name('stationcode-create')->middleware('role:admin');
  Route::get('/stationcode/{id}', [StationcodeController::class, 'show'])->name('stationcode-single')->middleware('role:admin,manager'); // json response for same page
  Route::put('/stationcode/{id}', [StationcodeController::class, 'update'])->name('stationcode-update')->middleware('role:admin');
  Route::delete('/stationcode/{id}', [StationcodeController::class, 'destroy'])->name('stationcode-delete')->middleware('role:admin');
});
