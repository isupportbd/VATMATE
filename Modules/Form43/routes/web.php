<?php

use Illuminate\Support\Facades\Route;
use Modules\Form43\Http\Controllers\Form43Controller;

// Route::middleware(['auth', 'verified'])->group(function () {
//   Route::resource('form43s', Form43Controller::class)->names('form43');
// });

Route::middleware(['auth'])->group(function () {
  Route::get('/form43-dropdown', [Form43Controller::class, 'form43Dropdown'])->name('form43-dropdown')->middleware('role:admin,manager'); // json response for same page and for dropdown
  Route::get('/form43', [Form43Controller::class, 'index'])->name('form43')->middleware('role:admin,manager');
  // Route::get('/form43-specific-import', [Form43Controller::class, 'form43ClientItemCriteriaImport'])->name('form43-specific-import')->middleware('role:admin,manager');
  // Route::get('/form43-specific-local', [Form43Controller::class, 'form43ClientItemCriteriaLocal'])->name('form43-specific-local')->middleware('role:admin,manager');
  Route::get('/form43-last', [Form43Controller::class, 'form43ClientItemLast'])->name('form43-last')->middleware('role:admin,manager');
  Route::post('/form43', [Form43Controller::class, 'store'])->name('form43-create')->middleware('role:admin,manager');
  Route::get('/form43/{id}', [Form43Controller::class, 'show'])->name('form43-single')->middleware('role:admin,manager'); // json response for same page
  Route::put('/form43/{id}', [Form43Controller::class, 'update'])->name('form43-update')->middleware('role:admin,manager');
  Route::delete('/form43/{id}', [Form43Controller::class, 'destroy'])->name('form43-delete')->middleware('role:admin,manager');
});
