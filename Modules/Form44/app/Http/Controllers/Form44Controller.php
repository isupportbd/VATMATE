<?php

namespace Modules\Form44\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class Form44Controller extends Controller
{
  /**
   * Display a listing of the resource.
   */
  public function index()
  {
    return Inertia("form44/index");
  }

  /**
   * 4.4 need lc and boe -> stock minus
   */
  public function store(Request $request) {}

  /**
   * Show the specified resource.
   */
  public function show($id)
  {
    return view('form44::show');
  }

  /**
   * 4.4 need lc and boe -> stock minus
   */
  public function update(Request $request, $id) {}

  /**
   * Remove the specified resource from storage.
   */
  public function destroy($id) {}
}
