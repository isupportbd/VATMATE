<?php

namespace Modules\Division\Http\Controllers;

use App\Events\CommonEvent;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Division\Models\Division;

class DivisionController extends Controller
{
  public $division;
  public function __construct(Division $division)
  {
    $this->division = $division;
  }

  // function call automatically from division page
  public function index(Request $request)
  {
    $search = trim((string) $request->input('search', ''));
    $termLike   = $search !== '' ? "%$search%" : null;

    $division = $this->division->when($search !== '', fn($query) => $query->where('name', 'ilike', $termLike))
      ->orderBy('id', 'desc')
      ->paginate((int)request('size', 10))
      ->withQueryString();

    return Inertia("division/index", ['division' => $division, 'search' => $search]);
  }

  // function call from division for create
  public function store(Request $request)
  {
    $user = Auth::user();

    $formfield = $request->validate(
      ['name' => 'required|unique:divisions,name|max:30'],
      [
        'name.required' => 'Name required',
        'name.unique' => 'Already exists',
        'name.max' => 'Max 30 char'
      ]
    );

    // realtime data
    event(new CommonEvent($user->id));

    $this->division->create($formfield);
    // return Redirect::route('division')->with('message', 'Division created'); // it is fetch again all data
    return back()->with('message', 'Division created'); // it is fetch same page not all data
  }

  // function call from division page as api for same page
  public function show($id)
  {
    $result = $this->division->find($id);
    $msg = $result ? ['division' => $result] : ['msg' => 'Nothing found'];
    return response()->json($msg);
    // return inertia("division/index", ['single' => $result]);
  }

  // function call form division page for update
  public function update(Request $request, $id)
  {
    $user = Auth::user();

    $formfield = $request->validate(
      ['name' => 'required|unique:divisions,name|max:30'],
      [
        'name.required' => 'Name required',
        'name.unique' => 'Already exists',
        'name.max' => 'Max 30 char'
      ]
    );

    $this->division->where('id', $id)->update($formfield);

    // realtime data
    event(new CommonEvent($user->id));

    return back()->with('message', 'Division updated');
  }

  // function call from division page for delete
  public function destroy($id)
  {
    $user = Auth::user();

    $ids = explode(',', $id);
    $this->division->whereIn('id', $ids)->delete();

    // realtime data
    event(new CommonEvent($user->id));

    return back()->with('message', 'Division deleted');
  }

  // function call for dropdown. it needs SelectInfinite component. It is send json response
  public function divisionDropdown(Request $request)
  {
    $search = trim((string) $request->input('search', ''));
    $termLike   = $search !== '' ? "%$search%" : null;

    $division = $this->division->query()
      ->when($search !== '', fn($query) => $query->where('name', 'ilike', $termLike))
      ->orderBy('id', 'desc')
      ->paginate((int)request('size') ? (int)request('size') : 5);

    return response()->json(['division' => $division]);
  }
}
