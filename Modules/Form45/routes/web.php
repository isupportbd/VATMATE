<?php

use Illuminate\Support\Facades\Route;
use Modules\Form45\Http\Controllers\Form45Controller;

// Route::middleware(['auth', 'verified'])->group(function () {
//   Route::resource('form45s', Form45Controller::class)->names('form45');
// });

Route::middleware(['auth'])->group(function () {
  Route::get('/form45', [Form45Controller::class, 'index'])->name('form45')->middleware('role:admin,manager');
  Route::post('/form45', [Form45Controller::class, 'store'])->name('form45-create')->middleware('role:admin,manager');
  Route::get('/form45/{id}', [Form45Controller::class, 'show'])->name('form45-single')->middleware('role:admin,manager'); // json response for same page
  Route::put('/form45/{id}', [Form45Controller::class, 'update'])->name('form45-update')->middleware('role:admin,manager');
  Route::delete('/form45/{id}', [Form45Controller::class, 'destroy'])->name('form45-delete')->middleware('role:admin,manager');
});
