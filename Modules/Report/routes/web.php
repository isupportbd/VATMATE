<?php

use Illuminate\Support\Facades\Route;
use Modules\Report\Http\Controllers\BoeController;
use Modules\Report\Http\Controllers\CreditnoteController;
use Modules\Report\Http\Controllers\DebitnoteController;
use Modules\Report\Http\Controllers\FileController;
use Modules\Report\Http\Controllers\Form44Controller;
use Modules\Report\Http\Controllers\Form45Controller;
use Modules\Report\Http\Controllers\Report43Controller;
use Modules\Report\Http\Controllers\Report621Controller;
use Modules\Report\Http\Controllers\Report63Controller;
use Modules\Report\Http\Controllers\Report91Controller;
use Modules\Report\Http\Controllers\ReportController;
use Modules\Report\Http\Controllers\Settlement91Controller;
use Modules\Report\Http\Controllers\SummaryController;

// Route::middleware(['auth', 'verified'])->group(function () {
//   Route::resource('reports', ReportController::class)->names('report');
// });

Route::middleware(['auth'])->group(function () {
  Route::get('/report', [ReportController::class, 'index'])->name('report')->middleware('role:admin,manager');
  Route::get('/report-client-data-list/{client_id}', [ReportController::class, 'clientDataList'])->name('report-client-data-list')->middleware('role:admin,manager');

  // report 6.2.1 filte, download with excel
  Route::get('/report-621/{client_id}', [Report621Controller::class, 'index'])->name('report-621')->middleware('role:admin,manager');
  Route::post('/report-621-excel', [Report621Controller::class, 'Report621Excel'])->name('report-621-excel')->middleware('role:admin,manager');
  Route::get('/report-621-download-excel/{file_name}', [Report621Controller::class, 'Report621DownloadExcel'])->name('report-621-download-excel')->middleware('role:admin,manager');

  // summary report
  Route::get('/report-summary-pdf', [SummaryController::class, 'pdf'])->name('report-summary-pdf')->middleware('role:admin,manager');
  Route::get('/report-summary/{client_id}/{monthYear}', [SummaryController::class, 'index'])->name('report-summary')->middleware('role:admin,manager');

  // report 9.1
  Route::get('/report-91/{client_id}/{monthYear}', [Report91Controller::class, 'index'])->name('report-91')->middleware('role:admin,manager');

  // boe report
  Route::get('/report-boe/{client_id}/{monthYear}', [BoeController::class, 'index'])->name('report-boe')->middleware('role:admin,manager');

  // report 6.3
  Route::get('/report-63-pdf', [Report63Controller::class, 'pdf'])->name('report-63-pdf')->middleware('role:admin,manager');
  Route::get('/report-63-view/{id}', [Report63Controller::class, 'show'])->name('report-63-view')->middleware('role:admin,manager');
  Route::get('/report-63/{client_id}/{monthYear}', [Report63Controller::class, 'index'])->name('report-63')->middleware('role:admin,manager');
  Route::post('/report-63-excel', [Report63Controller::class, 'Report63Excel'])->name('report-63-excel')->middleware('role:admin,manager');
  Route::get('/report-63-download-excel/{file_name}', [Report63Controller::class, 'Report63DownloadExcel'])->name('report-63-download-excel')->middleware('role:admin,manager');

  // report 4.3
  Route::get('/report-43-pdf', [Report43Controller::class, 'pdf'])->name('report-43-pdf')->middleware('role:admin,manager');
  Route::get('/report-43-view/{id}', [Report43Controller::class, 'show'])->name('report-43-view')->middleware('role:admin,manager');
  Route::get('/report-43-forwarding/{id}', [Report43Controller::class, 'forwarding'])->name('report-43-forwarding')->middleware('role:admin,manager');
  Route::get('/report-43/{client_id}/{monthYear}', [Report43Controller::class, 'index'])->name('report-43')->middleware('role:admin,manager');

  // creditnote 6.7
  Route::get('/creditnote-67-view/{id}', [CreditnoteController::class, 'show'])->name('creditnote-67-view')->middleware('role:admin,manager');
  Route::get('/creditnote-67/{client_id}/{monthYear}', [CreditnoteController::class, 'index'])->name('creditnote-67')->middleware('role:admin,manager');

  // debitnote 6.8
  Route::get('/debitnote-68-view/{id}', [DebitnoteController::class, 'show'])->name('debitnote-68-view')->middleware('role:admin,manager');
  Route::get('/debitnote-68/{client_id}/{monthYear}', [DebitnoteController::class, 'index'])->name('debitnote-68')->middleware('role:admin,manager');

  // form 4.4
  Route::get('/form-44-view/{id}', [Form44Controller::class, 'show'])->name('form-44-view')->middleware('role:admin,manager');
  Route::get('/form-44/{client_id}/{monthYear}', [Form44Controller::class, 'index'])->name('form-44')->middleware('role:admin,manager');

  // form 4.5
  Route::get('/form-45-view/{id}', [Form45Controller::class, 'show'])->name('form-45-view')->middleware('role:admin,manager');
  Route::get('/form-45/{client_id}/{monthYear}', [Form45Controller::class, 'index'])->name('form-45')->middleware('role:admin,manager');

  // file upload and download
  Route::get('/report-files', [FileController::class, 'index'])->name('report-files')->middleware('role:admin,manager');
  Route::post('/report-file-store', [FileController::class, 'store'])->name('report-file-store')->middleware('role:admin');
  Route::delete('/report-file-destroy/{file}', [FileController::class, 'destroy'])->name('report-file-destroy')->middleware('role:admin');
  Route::get('/report-file-download/{file}', [FileController::class, 'download'])->name('report-file-download')->middleware('role:admin');
});
