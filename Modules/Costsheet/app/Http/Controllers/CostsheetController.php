<?php

namespace Modules\Costsheet\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
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
      ->orderBy('id', 'desc')
      ->paginate((int)request('size', 10));

    return Inertia("costsheet/index", ['costsheet' => $costsheet, 'search' => $search]);
  }

  // function call from costsheet for create
  public function store(Request $request)
  {
    $formfield = $request->validate(
      ['name' => 'required|unique:costsheets,name|max:200'],
      [
        'name.required' => 'Name required',
        'name.unique' => 'Already exists',
        'name.max' => 'Max 200 char'
      ]
    );

    $this->costsheet->create($formfield);
    // return Redirect::route('costsheet')->with('message', 'costsheet created'); // it is fetch again all data
    return back()->with('message', 'Costsheet created'); // it is fetch same page not all data
  }

  // function call from costsheet page as api for same page
  public function show($id)
  {
    $result = $this->costsheet->find($id);
    $msg = $result ? ['costsheet' => $result] : ['msg' => 'Nothing found'];
    return response()->json($msg);
    // return inertia("costsheet/index", ['single' => $result]);
  }

  // function call form costsheet page for update
  public function update(Request $request, $id)
  {
    $formfield = $request->validate(
      ['name' => 'required|unique:costsheets,name|max:200'],
      [
        'name.required' => 'Name required',
        'name.unique' => 'Already exists',
        'name.max' => 'Max 200 char'
      ]
    );

    $this->costsheet->where('id', $id)->update($formfield);
    return back()->with('message', 'Costsheet updated');
  }

  // function call from costsheet page for delete
  public function destroy($id)
  {
    $ids = explode(',', $id);
    $this->costsheet->whereIn('id', $ids)->delete();
    return back()->with('message', 'Costsheet deleted');
  }

  // function call for dropdown. it needs SelectInfinite component. It is send json response
  public function costsheetDropdown(Request $request)
  {
    $search = $request->input('search');
    $costsheet = $this->costsheet->query()
      ->when($search, fn($query) => $query->where('name', 'like', '%' . strtolower($search) . '%'))
      ->orderBy('id', 'asc')
      ->paginate((int)request('size') ? (int)request('size') : 10);

    return response()->json(['costsheet' => $costsheet]);
  }
}
