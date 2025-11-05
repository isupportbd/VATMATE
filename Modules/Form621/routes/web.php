<?php

use Illuminate\Support\Facades\Route;
use Modules\Form621\Http\Controllers\Form621Controller;

// Route::middleware(['auth', 'verified'])->group(function () {
//   Route::resource('form621s', Form621Controller::class)->names('form621');
// });

Route::middleware(['auth'])->group(function () {
  Route::get('/form621', [Form621Controller::class, 'index'])->name('form621')->middleware('role:admin,manager');
  Route::get('/form621/distinct/client', [Form621Controller::class, 'indexDistinctClient'])->name('form621-distinct-client')->middleware('role:admin,manager');
  Route::get('/form621/distinct/item', [Form621Controller::class, 'indexDistinctItem'])->name('form621-distinct-item')->middleware('role:admin,manager');
  Route::get('/form621/client/single/alldata/{clientbin_id}', [Form621Controller::class, 'indexSingleClient'])->name('form621-client-single-alldata')->middleware('role:admin,manager');
  Route::get('/form621/client/lastdate', [Form621Controller::class, 'indexClientLastDate'])->name('form621-client-lastdate')->middleware('role:admin,manager');
  Route::get('/form621/client/item/laststock', [Form621Controller::class, 'indexClientItemLastStock'])->name('form621-client-item-laststock')->middleware('role:admin,manager');
  Route::get('/form621/client/item/secondlast', [Form621Controller::class, 'indexClientItemSecondLast'])->name('form621-client-item-secondlast')->middleware('role:admin,manager');
  Route::get('/form621/distinct/client/report621', [Form621Controller::class, 'indexDistinctClientReportform621'])->name('form621-distinct-client-report621')->middleware('role:admin,manager');
  Route::get('/form621/client/single/alldata/report621/{clientbin_id}', [Form621Controller::class, 'indexSingleClientReportform621'])->name('form621-client-single-alldata-report621')->middleware('role:admin,manager');
});
