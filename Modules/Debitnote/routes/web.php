<?php

use Illuminate\Support\Facades\Route;
use Modules\Debitnote\Http\Controllers\DebitnoteController;

// Route::middleware(['auth', 'verified'])->group(function () {
//   Route::resource('debitnotes', DebitnoteController::class)->names('debitnote');
// });

Route::middleware(['auth'])->group(function () {
  Route::get('/debitnote', [DebitnoteController::class, 'index'])->name('debitnote')->middleware('role:admin,manager');
  Route::post('/debitnote', [DebitnoteController::class, 'store'])->name('debitnote-create')->middleware('role:admin,manager');
  Route::get('/debitnote/{id}', [DebitnoteController::class, 'show'])->name('debitnote-single')->middleware('role:admin,manager'); // json response for same page
  Route::put('/debitnote/{id}', [DebitnoteController::class, 'update'])->name('debitnote-update')->middleware('role:admin,manager');
  Route::delete('/debitnote/{id}', [DebitnoteController::class, 'destroy'])->name('debitnote-delete')->middleware('role:admin,manager');
});
