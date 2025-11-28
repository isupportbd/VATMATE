<?php

use Illuminate\Support\Facades\Route;
use Modules\Form621\Http\Controllers\Form621Controller;

// Route::middleware(['auth', 'verified'])->group(function () {
//   Route::resource('form621s', Form621Controller::class)->names('form621');
// });

Route::middleware(['auth'])->group(function () {
  // index for all user by defing id
  Route::get('/form621', [Form621Controller::class, 'index'])->name('form621')->middleware('role:admin,manager');

  // last item combination with client item and last = 1
  Route::get('/form621-last', [Form621Controller::class, 'form621Last'])->name('form621-last')->middleware('role:admin,manager');

  // invoice data combination with client item and invoice
  Route::get('/form621-client-item-invoice', [Form621Controller::class, 'creditnoteResponse'])->name('form621-client-item-invoice')->middleware('role:admin,manager');

  // lc data combination with client item lc and boe
  Route::get('/form621-client-item-lc-boe', [Form621Controller::class, 'debitnoteResponse'])->name('form621-client-item-lc-boe')->middleware('role:admin,manager');

  // only lc
  Route::get('/form621-client-lc-inv-dropdown', [Form621Controller::class, 'form621LcInvByClient'])->name('form621-client-lc-inv-dropdown')->middleware('role:admin,manager');

  // only invoice
  Route::get('/form621-client-lc-inv-item-dropdown', [Form621Controller::class, 'form621ItemsByClientLcInv'])->name('form621-client-lc-inv-item-dropdown')->middleware('role:admin,manager');

  // common lc or invoice for client
  Route::get('/form621-client-lc-item-boe-dropdown', [Form621Controller::class, 'form621BoesByClientLcItem'])->name('form621-client-lc-item-boe-dropdown')->middleware('role:admin,manager');

  // single client all data grab
  Route::get('/form621-client-single-index/{client_id}', [Form621Controller::class, 'clientSingleIndex'])->name('form621-client-single-index')->middleware('role:admin,manager');
});
