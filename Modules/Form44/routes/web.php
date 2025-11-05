<?php

use Illuminate\Support\Facades\Route;
use Modules\Form44\Http\Controllers\Form44Controller;

// Route::middleware(['auth', 'verified'])->group(function () {
//   Route::resource('form44s', Form44Controller::class)->names('form44');
// });

Route::middleware(['auth'])->group(function () {
  Route::get('/form44', [Form44Controller::class, 'index'])->name('form44')->middleware('role:admin,manager');
  Route::post('/form44', [Form44Controller::class, 'store'])->name('form44-create')->middleware('role:admin,manager');
  Route::get('/form44/{id}', [Form44Controller::class, 'show'])->name('form44-single')->middleware('role:admin,manager'); // json response for same page
  Route::put('/form44/{id}', [Form44Controller::class, 'update'])->name('form44-update')->middleware('role:admin,manager');
  Route::delete('/form44/{id}', [Form44Controller::class, 'destroy'])->name('form44-delete')->middleware('role:admin,manager');
});
