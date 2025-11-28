<?php

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//   return $request->user();
// })->middleware('auth:sanctum');

Route::group(['middleware' => 'auth:api', 'prefix' => 'v1'], function ($router) {
  Route::post('/mailsend', [AuthController::class, 'mailSend'])->withoutMiddleware('auth:api');
});
