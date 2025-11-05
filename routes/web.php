<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\RoleController;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });

Route::middleware(['guest'])->group(function () {
  // view route
  Route::get('/login', [AuthController::class, 'login'])->name('login');
  Route::get('/forgetpassword', [AuthController::class, 'forgetPassword'])->name('forget-password');
  Route::get('/resetpassword', [AuthController::class, 'resetPassword'])->name('reset-password');

  // action route
  Route::post('/userlogin', [AuthController::class, 'userLogin'])->name('user-login');
  Route::post('/userforgetpassword', [AuthController::class, 'userForgetPassword'])->name('user-forgetpassword');
  // Route::post('/mailsend', [AuthController::class, 'mailSend'])->name('mail-send');
});

Route::middleware(['auth'])->group(function () {
  // view route
  Route::get('/authentication/profile-get', [AuthController::class, 'userProfile'])->name('profile-get')->middleware('role:superadmin,admin,editor,user');

  // action route
  Route::post('/userlogout', [AuthController::class, 'userLogout'])->name('user-logout');
  Route::get('/user-dropdown', [AuthController::class, 'userDropdown'])->name('user-dropdown')->middleware('role:superadmin,admin'); // json response for same page and for dropdown
  Route::get('/user', [AuthController::class, 'index'])->name('user')->middleware('role:superadmin,admin');
  Route::post('/user', [AuthController::class, 'store'])->name('user-create')->middleware('role:superadmin,admin');
  Route::put('/user/profile', [AuthController::class, 'profileUpdate'])->name('user-profile')->middleware('role:superadmin,admin,editor,user');
  Route::get('/user/{id}', [AuthController::class, 'show'])->name('user-single')->middleware('role:superadmin,admin'); // json response for same page
  Route::put('/user/{id}', [AuthController::class, 'update'])->name('user-update')->middleware('role:superadmin,admin');
  Route::delete('/user/{id}', [AuthController::class, 'destroy'])->name('user-delete')->middleware('role:superadmin,admin');
  Route::put('/user/status/{id}', [AuthController::class, 'statusUpdate'])->name('user-status')->middleware('role:superadmin,admin');

  Route::get('/role', [RoleController::class, 'index'])->name('role-dropdown')->middleware('role:superadmin,admin');
  Route::get('/role/{id}', [RoleController::class, 'show'])->name('role-single')->middleware('role:superadmin,admin');

  // jobs route
  // Route::post('/jobs-truncate', [JobController::class, 'jobsTableTruncate'])->name('jobs-table-truncate')->middleware('role:superadmin');
  // Route::get('/failed-jobs', [JobController::class, 'index'])->name('failed-jobs')->middleware('role:superadmin');
  // Route::post('/failed-jobs/retryall', [JobController::class, 'retryAll'])->name('failed-jobs-retry-all')->middleware('role:superadmin');
  // Route::delete('/failed-jobs/deleteall/flush', [JobController::class, 'flush'])->name('failed-jobs-delete-all')->middleware('role:superadmin');
  // Route::get('/failed-jobs/{id}', [JobController::class, 'show'])->name('failed-jobs-single')->middleware('role:superadmin');
  // Route::delete('/failed-jobs/{id}', [JobController::class, 'forget'])->name('failed-jobs-delete')->middleware('role:superadmin');
  // Route::post('/failed-jobs/{id}', [JobController::class, 'retry'])->name('failed-jobs-retry')->middleware('role:superadmin');
});
