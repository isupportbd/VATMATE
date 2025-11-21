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

  // combine lc and invoice
  Route::get('/form621-client-dropdown', [Form621Controller::class, 'form621ClientDropdown'])->name('form621-client-dropdown')->middleware('role:admin,manager');

  // only lc
  Route::get('/form621-client-lc-inv-dropdown', [Form621Controller::class, 'form621LcInvByClient'])->name('form621-client-lc-inv-dropdown')->middleware('role:admin,manager');

  // only invoice
  Route::get('/form621-client-lc-inv-item-dropdown', [Form621Controller::class, 'form621ItemsByClientLcInv'])->name('form621-client-lc-inv-item-dropdown')->middleware('role:admin,manager');

  // common lc or invoice for client
  Route::get('/form621-client-lc-item-boe-dropdown', [Form621Controller::class, 'form621BoesByClientLcItem'])->name('form621-client-lc-item-boe-dropdown')->middleware('role:admin,manager');

  // single client all data grab
  Route::get('/form621-client-single-index/{client_id}', [Form621Controller::class, 'clientSingleIndex'])->name('form621-client-single-index')->middleware('role:admin,manager');

  // // old route
  // Route::get('/form621-last-date', [Form621Controller::class, 'form621LastDate'])->name('form621-last-date')->middleware('role:admin,manager');
  // Route::get('/form621-client-distinct', [Form621Controller::class, 'clientDistinctIndex'])->name('form621-client-distinct')->middleware('role:admin,manager');
  // Route::get('/form621/distinct/item', [Form621Controller::class, 'indexDistinctItem'])->name('form621-distinct-item')->middleware('role:admin,manager');
  // Route::get('/form621/client/lastdate', [Form621Controller::class, 'indexClientLastDate'])->name('form621-client-lastdate')->middleware('role:admin,manager');
  // Route::get('/form621/client/item/laststock', [Form621Controller::class, 'indexClientItemLastStock'])->name('form621-client-item-laststock')->middleware('role:admin,manager');
  // Route::get('/form621/client/item/secondlast', [Form621Controller::class, 'indexClientItemSecondLast'])->name('form621-client-item-secondlast')->middleware('role:admin,manager');
  // Route::get('/form621/distinct/client/report621', [Form621Controller::class, 'indexDistinctClientReportform621'])->name('form621-distinct-client-report621')->middleware('role:admin,manager');
  // Route::get('/form621/client/single/alldata/report621/{clientbin_id}', [Form621Controller::class, 'indexSingleClientReportform621'])->name('form621-client-single-alldata-report621')->middleware('role:admin,manager');
});
