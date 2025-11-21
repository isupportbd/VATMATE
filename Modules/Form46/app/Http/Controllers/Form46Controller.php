<?php

namespace Modules\Form46\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class Form46Controller extends Controller
{
  /**
   * Display a listing of the resource.
   */
  public function index()
  {
    return Inertia("form46/index");
  }

  /**
   * 4.6 need lc and boe or invoice -> stock minus
   */
  public function store(Request $request) {}

  /**
   * Show the specified resource.
   */
  public function show($id)
  {
    return view('form46::show');
  }

  /**
   * 4.6 need lc and boe or invoice -> stock minus
   */
  public function update(Request $request, $id) {}

  /**
   * Remove the specified resource from storage.
   */
  public function destroy($id) {}
}
