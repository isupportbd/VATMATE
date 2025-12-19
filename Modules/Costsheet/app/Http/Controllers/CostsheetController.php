<?php

namespace Modules\Costsheet\Http\Controllers;

use App\Events\CommonEvent;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
    $search = trim((string) $request->input('search', ''));
    $termLike   = $search !== '' ? $search . '%' : null;

    $costsheet = $this->costsheet->when($search, fn($query) => $query->where('name', 'like', $termLike))
      ->orderBy('id', 'desc')
      ->paginate((int)request('size', 10))
      ->withQueryString();

    return Inertia("costsheet/index", ['costsheet' => $costsheet, 'search' => $search]);
  }

  // function call from costsheet for create
  public function store(Request $request)
  {
    $user = Auth::user();

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

    // realtime data
    event(new CommonEvent($user->id));

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
    $user = Auth::user();

    $formfield = $request->validate(
      ['name' => 'required|unique:costsheets,name|max:200'],
      [
        'name.required' => 'Name required',
        'name.unique' => 'Already exists',
        'name.max' => 'Max 200 char'
      ]
    );

    $this->costsheet->where('id', $id)->update($formfield);

    // realtime data
    event(new CommonEvent($user->id));

    return back()->with('message', 'Costsheet updated');
  }

  // function call from costsheet page for delete
  public function destroy($id)
  {
    $user = Auth::user();

    $ids = explode(',', $id);

    $this->costsheet->whereIn('id', $ids)->delete();

    // realtime data
    event(new CommonEvent($user->id));

    return back()->with('message', 'Costsheet deleted');
  }

  // function call for dropdown. it needs SelectInfinite component. It is send json response
  public function costsheetDropdown(Request $request)
  {
    $search = trim((string) $request->input('search', ''));
    $termLike   = $search !== '' ? $search . '%' : null;

    $costsheet = $this->costsheet->query()
      ->when($search, fn($query) => $query->where('name', 'like', $termLike))
      ->orderBy('id', 'asc')
      ->paginate((int)request('size') ? (int)request('size') : 10);

    return response()->json(['costsheet' => $costsheet]);
  }
}
