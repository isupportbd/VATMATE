<?php

use Illuminate\Support\Facades\Route;
use Modules\Mushaknote\Http\Controllers\MushaknoteController;

// Route::middleware(['auth', 'verified'])->group(function () {
//   Route::resource('mushaknotes', MushaknoteController::class)->names('mushaknote');
// });

Route::middleware(['auth'])->group(function () {
  Route::get('/mushaknote-dropdown', [MushaknoteController::class, 'mushakDropdown'])->name('mushaknote-dropdown')->middleware('role:admin,manager');
  Route::get('/mushaknote/{id}', [MushaknoteController::class, 'show'])->name('mushaknote-single')->middleware('role:admin,manager');
});
