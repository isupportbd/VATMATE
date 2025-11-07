<?php

namespace Modules\Costsheet\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Costsheet\Models\Costsheet;

class CostsheetController extends Controller
{
  public $costsheet;
  public function __construct(Costsheet $costsheet)
  {
    $this->costsheet = $costsheet;
  }

  /**
   * Display a listing of the resource.
   */
  public function index(Request $request)
  {
    $search = $request->input('search');
    $costsheet = $this->costsheet->when($search, fn($query) => $query->where('name', 'like', '%' . strtolower($search) . '%'))
      ->orderBy('id', 'asc')
      ->paginate((int)request('size', 5));

    return response()->json(['costsheet' => $costsheet]);
  }

  /**
   * Show the specified resource.
   */
  public function show($id)
  {
    $result = $this->costsheet->find($id);
    $msg = $result ? ['costsheet' => $result] : ['msg' => 'Nothing found'];
    return response()->json($msg);
    // return inertia("costsheet/index", ['single' => $result]);
  }
}
