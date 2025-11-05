<?php

use Illuminate\Support\Facades\Route;
use Modules\Form46\Http\Controllers\Form46Controller;

// Route::middleware(['auth', 'verified'])->group(function () {
//   Route::resource('form46s', Form46Controller::class)->names('form46');
// });

Route::middleware(['auth'])->group(function () {
  Route::get('/form46', [Form46Controller::class, 'index'])->name('form46')->middleware('role:admin,manager');
  Route::post('/form46', [Form46Controller::class, 'store'])->name('form46-create')->middleware('role:admin,manager');
  Route::get('/form46/{id}', [Form46Controller::class, 'show'])->name('form46-single')->middleware('role:admin,manager'); // json response for same page
  Route::put('/form46/{id}', [Form46Controller::class, 'update'])->name('form46-update')->middleware('role:admin,manager');
  Route::delete('/form46/{id}', [Form46Controller::class, 'destroy'])->name('form46-delete')->middleware('role:admin,manager');
});
