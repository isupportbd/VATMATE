<?php

namespace Modules\Form45\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class Form45Controller extends Controller
{
  /**
   * Display a listing of the resource.
   */
  public function index()
  {
    return Inertia("form45/index");
  }

  /**
   * 4.5 need lc and boe or invoice -> stock minus
   */
  public function store(Request $request) {}

  /**
   * Show the specified resource.
   */
  public function show($id)
  {
    return view('form45::show');
  }

  /**
   * 4.5 need lc and boe or invoice -> stock minus
   */
  public function update(Request $request, $id) {}

  /**
   * Remove the specified resource from storage.
   */
  public function destroy($id) {}
}
