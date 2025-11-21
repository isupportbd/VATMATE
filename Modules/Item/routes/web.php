<?php

use Illuminate\Support\Facades\Route;
use Modules\Item\Http\Controllers\ItemController;

// Route::middleware(['auth', 'verified'])->group(function () {
//   Route::resource('items', ItemController::class)->names('item');
// });

Route::middleware(['auth'])->group(function () {
  Route::get('/item-dropdown', [ItemController::class, 'itemDropdown'])->name('item-dropdown')->middleware('role:admin,manager'); // json response for same page and for dropdown
  Route::get('/item', [ItemController::class, 'index'])->name('item')->middleware('role:admin');
  Route::post('/item', [ItemController::class, 'store'])->name('item-create')->middleware('role:admin');
  Route::get('/item/{id}', [ItemController::class, 'show'])->name('item-single')->middleware('role:admin'); // json response for same page
  Route::put('/item/{id}', [ItemController::class, 'update'])->name('item-update')->middleware('role:admin');
  Route::delete('/item/{id}', [ItemController::class, 'destroy'])->name('item-delete')->middleware('role:admin');
});
