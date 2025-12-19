<?php

use Illuminate\Support\Facades\Route;
use Modules\Report\Http\Controllers\BoeController;
use Modules\Report\Http\Controllers\FileController;
use Modules\Report\Http\Controllers\Report43Controller;
use Modules\Report\Http\Controllers\Report44Controller;
use Modules\Report\Http\Controllers\Report45Controller;
use Modules\Report\Http\Controllers\Report621Controller;
use Modules\Report\Http\Controllers\Report63Controller;
use Modules\Report\Http\Controllers\Report67Controller;
use Modules\Report\Http\Controllers\Report68Controller;
use Modules\Report\Http\Controllers\Report91Controller;
use Modules\Report\Http\Controllers\ReportController;
use Modules\Report\Http\Controllers\SummaryController;

// Route::middleware(['auth', 'verified'])->group(function () {
//   Route::resource('reports', ReportController::class)->names('report');
// });

Route::middleware(['auth'])->group(function () {
  Route::get('/report', [ReportController::class, 'index'])->name('report')->middleware('role:admin,manager');
  Route::get('/report-client-data-list/{client_id}', [ReportController::class, 'clientDataList'])->name('report-client-data-list')->middleware('role:admin,manager');

  // report 6.2.1 filte, download with excel
  Route::get('/report-621/{client_id}/{monthYear}', [Report621Controller::class, 'index'])->name('report-621')->middleware('role:admin,manager');
  Route::post('/report-621-excel', [Report621Controller::class, 'Report621Excel'])->name('report-621-excel')->middleware('role:admin,manager');
  Route::get('/report-621-download-excel/{file_name}', [Report621Controller::class, 'Report621DownloadExcel'])->name('report-621-download-excel')->middleware('role:admin,manager');

  // summary report
  Route::get('/report-summary-pdf', [SummaryController::class, 'pdf'])->name('report-summary-pdf')->middleware('role:admin,manager');
  Route::get('/report-summary/{client_id}/{monthYear}', [SummaryController::class, 'index'])->name('report-summary')->middleware('role:admin,manager');

  // report 9.1
  Route::get('/report-91-pdf', [Report91Controller::class, 'pdf'])->name('report-91-pdf')->middleware('role:admin,manager');
  Route::get('/report-91/{client_id}/{monthYear}', [Report91Controller::class, 'index'])->name('report-91')->middleware('role:admin,manager');

  // boe report
  Route::get('/boe-pdf', [BoeController::class, 'pdf'])->name('boe-pdf')->middleware('role:admin,manager');
  Route::get('/report-boe/{client_id}/{monthYear}', [BoeController::class, 'index'])->name('report-boe')->middleware('role:admin,manager');

  // report 6.3
  Route::get('/report-63-pdf', [Report63Controller::class, 'pdf'])->name('report-63-pdf')->middleware('role:admin,manager');
  Route::get('/report-63-view/{id}', [Report63Controller::class, 'show'])->name('report-63-view')->middleware('role:admin,manager');
  Route::get('/report-63/{client_id}/{monthYear}', [Report63Controller::class, 'index'])->name('report-63')->middleware('role:admin,manager');
  Route::post('/report-63-excel', [Report63Controller::class, 'Report63Excel'])->name('report-63-excel')->middleware('role:admin,manager');
  Route::get('/report-63-download-excel/{file_name}', [Report63Controller::class, 'Report63DownloadExcel'])->name('report-63-download-excel')->middleware('role:admin,manager');

  // report 4.3
  Route::get('/report-43-pdf', [Report43Controller::class, 'pdf'])->name('report-43-pdf')->middleware('role:admin,manager');
  Route::get('/report-43-pdf-forwarding', [Report43Controller::class, 'pdfForwarding'])->name('report-43-pdf-forwarding')->middleware('role:admin,manager');
  Route::get('/report-43-view/{id}', [Report43Controller::class, 'show'])->name('report-43-view')->middleware('role:admin,manager');
  Route::get('/report-43-forwarding/{id}', [Report43Controller::class, 'forwarding'])->name('report-43-forwarding')->middleware('role:admin,manager');
  Route::get('/report-43/{client_id}', [Report43Controller::class, 'index'])->name('report-43')->middleware('role:admin,manager');

  // creditnote 6.7
  Route::get('/report-67-pdf', [Report67Controller::class, 'pdf'])->name('report-67-pdf')->middleware('role:admin,manager');
  Route::get('/report-67-view/{id}', [Report67Controller::class, 'show'])->name('report-67-view')->middleware('role:admin,manager');
  Route::get('/report-67/{client_id}/{monthYear}', [Report67Controller::class, 'index'])->name('report-67')->middleware('role:admin,manager');

  // debitnote 6.8
  Route::get('/report-68-pdf', [Report68Controller::class, 'pdf'])->name('report-68-pdf')->middleware('role:admin,manager');
  Route::get('/report-68-view/{id}', [Report68Controller::class, 'show'])->name('report-68-view')->middleware('role:admin,manager');
  Route::get('/report-68/{client_id}/{monthYear}', [Report68Controller::class, 'index'])->name('report-68')->middleware('role:admin,manager');

  // form 4.4
  Route::get('/report-44-pdf', [Report44Controller::class, 'pdf'])->name('report-44-pdf')->middleware('role:admin,manager');
  Route::get('/report-44-view/{id}', [Report44Controller::class, 'show'])->name('report-44-view')->middleware('role:admin,manager');
  Route::get('/report-44/{client_id}/{monthYear}', [Report44Controller::class, 'index'])->name('report-44')->middleware('role:admin,manager');

  // form 4.5
  Route::get('/report-45-pdf', [Report45Controller::class, 'pdf'])->name('report-45-pdf')->middleware('role:admin,manager');
  Route::get('/report-45-view/{id}', [Report45Controller::class, 'show'])->name('report-45-view')->middleware('role:admin,manager');
  Route::get('/report-45/{client_id}/{monthYear}', [Report45Controller::class, 'index'])->name('report-45')->middleware('role:admin,manager');

  // file upload and download
  Route::get('/report-files', [FileController::class, 'index'])->name('report-files')->middleware('role:admin,manager');
  Route::post('/report-file-store', [FileController::class, 'store'])->name('report-file-store')->middleware('role:admin');
  Route::delete('/report-file-destroy/{file}', [FileController::class, 'destroy'])->name('report-file-destroy')->middleware('role:admin');
  Route::get('/report-file-download/{file}', [FileController::class, 'download'])->name('report-file-download')->middleware('role:admin,manager');
});
