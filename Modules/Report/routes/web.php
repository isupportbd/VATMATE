<?php

use Illuminate\Support\Facades\Route;
use Modules\Report\Http\Controllers\FileController;
use Modules\Report\Http\Controllers\Report621Controller;
use Modules\Report\Http\Controllers\ReportController;

// Route::middleware(['auth', 'verified'])->group(function () {
//   Route::resource('reports', ReportController::class)->names('report');
// });

Route::middleware(['auth'])->group(function () {
  Route::get('/report', [ReportController::class, 'index'])->name('report')->middleware('role:admin,manager');
  Route::get('/report-client-data-list/{client_id}', [ReportController::class, 'clientDataList'])->name('report-client-data-list')->middleware('role:admin,manager');

  // report 621 filter download with excel
  Route::get('/report-621/{client_id}', [Report621Controller::class, 'index'])->name('report-621')->middleware('role:admin,manager');
  Route::post('/report-621-excel', [Report621Controller::class, 'Report621Excel'])->name('report-621-excel')->middleware('role:admin,manager');
  Route::get('/report-621-download-excel/{file_name}', [Report621Controller::class, 'Report621DownloadExcel'])->name('report-621-download-excel')->middleware('role:admin,manager');

  // file upload and download
  Route::get('/report-files', [FileController::class, 'index'])->name('report-files')->middleware('role:admin,manager');
  Route::post('/report-file-store', [FileController::class, 'store'])->name('report-file-store')->middleware('role:admin');
  Route::delete('/report-file-destroy/{file}', [FileController::class, 'destroy'])->name('report-file-destroy')->middleware('role:admin');
  Route::get('/report-file-download/{file}', [FileController::class, 'download'])->name('report-file-download')->middleware('role:admin');

  // Route::get('/report/searchboe/import/import-search-boe', [ReportController::class, 'importSearchBoE'])->name('import-search-boe')->middleware('role:superadmin,admin,editor');
  // Route::get('/report/searchboe/local/local-search-memo', [ReportController::class, 'localSearchMemo'])->name('local-search-memo')->middleware('role:superadmin,admin,editor');
  // Route::get('/report/client/list', [ReportController::class, 'clientList'])->name('report-client-list')->middleware('role:admin,editor');
  // Route::get('/report/summary/list/{clientbin_id}', [ReportController::class, 'sumaryList'])->name('report-summary-list')->middleware('role:admin,editor');
  // Route::get('/report/report43-list', [ReportController::class, 'report43List'])->name('report-43-list')->middleware('role:admin,editor');
  // Route::get('/report/report43/{id}', [ReportController::class, 'report43'])->name('report-43')->middleware('role:admin,editor');
  // Route::get('/report/forwarding43/{id}', [ReportController::class, 'forwardingReport43'])->name('forwarding-43')->middleware('role:admin,editor');
  // Route::get('/report/localsale/{id}', [ReportController::class, 'report63Local'])->name('report-63-local')->middleware('role:admin,editor');
  // Route::get('/report/importersale/{id}', [ReportController::class, 'report63Import'])->name('report-63-import')->middleware('role:admin,editor');
  // Route::get('/report/summary/{id}/{monthyear}', [ReportController::class, 'reportSingleSummary'])->name('report-single-summary')->middleware('role:admin,editor');
  // Route::get('/report/purchase/details/{id}/{monthyear}', [ReportController::class, 'reportPurchaseDetail'])->name('report-purchase-details')->middleware('role:admin,editor');
  // Route::get('/report/report91/{id}/{monthyear}', [ReportController::class, 'reportSingle91'])->name('report-91-single')->middleware('role:admin,editor');
  // Route::get('/report/export/import621/{id}/{monthyear}', [ReportController::class, 'exportImport621'])->name('report-excell-import-621')->middleware('role:admin,editor'); // optional parameter has -> 'item_id'
  // Route::get('/report/export/local621/{id}/{monthyear}', [ReportController::class, 'exportLocal621'])->name('report-excell-local-621')->middleware('role:admin,editor'); // optional parameter has -> 'item_id'
  // Route::get('/report/transaction/editor', [ReportController::class, 'reportTransactionEditor'])->name('report-transaction-editor')->middleware('role:admin');
  // Route::get('/export/transaction/count/editor/{monthyear}', [ReportController::class, 'exportTransactionCountEditor'])->name('report-excell-transaction-count-editor')->middleware('role:admin');
  // Route::get('/export/transaction/editor/{monthyear}', [ReportController::class, 'exportTransactionEditor'])->name('report-excell-transaction-editor')->middleware('role:admin');
  // Route::get('/export/transaction/form43/{monthyear}', [ReportController::class, 'exportForm43Admin'])->name('report-excell-transaction-form43')->middleware('role:admin');
  // Route::get('/export/editor/import63/{id}/{monthyear}', [ReportController::class, 'exportImport63Editor'])->name('report-excell-editor-import-63')->middleware('role:admin,editor');
  // Route::get('/export/editor/local63/{id}/{monthyear}', [ReportController::class, 'exportLocal63Editor'])->name('report-excell-editor-local-63')->middleware('role:admin,editor');

  // // dashboard
  // Route::get('/dashboard/totaleditor', [ReportController::class, 'totalEditor'])->name('report-total-editor')->middleware('role:admin');
  // Route::get('/dashboard/totalclient', [ReportController::class, 'totalClient'])->name('report-total-client')->middleware('role:admin');
  // Route::get('/dashboard/totaltransaction/current', [ReportController::class, 'totalTransactionCurrent'])->name('report-total-transaction-current')->middleware('role:admin');
});
