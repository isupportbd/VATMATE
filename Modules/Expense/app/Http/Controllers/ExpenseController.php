<?php

namespace Modules\Expense\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Expense\Models\Expense;

class ExpenseController extends Controller
{
  public $expense;
  public function __construct(Expense $expense)
  {
    $this->expense = $expense;
  }

  /**
   * Display a listing of the resource.
   */
  public function index(Request $request)
  {
    $search = trim((string) $request->input('search', ''));
    $termLike   = $search !== '' ? $search . '%' : null;

    $expense = $this->expense->when($search !== '', fn($query) => $query->where('name', 'like', $termLike))
      ->orderBy('id', 'asc')
      ->paginate((int)request('size', 5));

    return response()->json(['expense' => $expense]);
  }

  /**
   * Show the specified resource.
   */
  public function show($id)
  {
    $result = $this->expense->find($id);
    $msg = $result ? ['expense' => $result] : ['msg' => 'Nothing found'];
    return response()->json($msg);
  }
}
