<?php

use Illuminate\Support\Facades\Route;
use Modules\Creditnote\Http\Controllers\CreditnoteController;

// Route::middleware(['auth', 'verified'])->group(function () {
//   Route::resource('creditnotes', CreditnoteController::class)->names('creditnote');
// });

Route::middleware(['auth'])->group(function () {
  Route::get('/creditnote', [CreditnoteController::class, 'index'])->name('creditnote')->middleware('role:admin,manager');
  Route::post('/creditnote', [CreditnoteController::class, 'store'])->name('creditnote-create')->middleware('role:admin,manager');
  Route::get('/creditnote/{id}', [CreditnoteController::class, 'show'])->name('creditnote-single')->middleware('role:admin,manager'); // json response for same page
  Route::put('/creditnote/{id}', [CreditnoteController::class, 'update'])->name('creditnote-update')->middleware('role:admin,manager');
  Route::delete('/creditnote/{id}', [CreditnoteController::class, 'destroy'])->name('creditnote-delete')->middleware('role:admin,manager');
});
