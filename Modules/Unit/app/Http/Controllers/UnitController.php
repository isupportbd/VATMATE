<?php

namespace Modules\Unit\Http\Controllers;

use App\Events\CommonEvent;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Unit\Models\Unit;

class UnitController extends Controller
{
  public $unit;
  public function __construct(Unit $unit)
  {
    $this->unit = $unit;
  }

  // function call automatically from unit page
  public function index(Request $request)
  {
    $search = trim((string) $request->input('search', ''));
    $termLike   = $search !== '' ? "%$search%" : null;

    $unit = $this->unit->when($search !== '', fn($query) => $query->where('name', 'ilike', $termLike))
      ->orderBy('id', 'desc')
      ->paginate((int)request('size', 10))
      ->withQueryString();

    return Inertia("unit/index", ['unit' => $unit, 'search' => $search]);
  }

  // function call from unit page for create
  public function store(Request $request)
  {
    $user = Auth::user();

    $formField = $request->validate(
      ['name' => 'required|unique:units,name|max:50'],
      [
        'name.required' => 'Unit required',
        'name.unique' => 'Exists, change your input',
        'name.max' => 'Max 50 char',
      ]
    );
    $this->unit->create($formField);

    // realtime data
    event(new CommonEvent($user->id));

    return back()->with('message', 'Unit created');
  }

  // function call from unit page as api for same page
  public function show($id)
  {
    $result = $this->unit->find($id);
    $msg = $result ? ['unit' => $result] : ['msg' => 'Nothing found'];
    return response()->json($msg);
    // return inertia("item/index", ['single' => $result]);
  }

  // function call form unit page for update
  public function update(Request $request, $id)
  {
    $user = Auth::user();

    $formField = $request->validate(
      ['name' => 'required|unique:units,name|max:50'],
      [
        'name.required' =>  'Unit required',
        'name.unique' => 'Exists, change your input',
        'name.max' => 'Max 50 char',
      ]
    );
    $this->unit->where('id', $id)->update($formField);

    // realtime data
    event(new CommonEvent($user->id));

    return back()->with('message', 'Unit updated');
  }

  // function call from unit page for delete
  public function destroy($id)
  {
    $user = Auth::user();

    $ids = explode(',', $id);

    // realtime data
    event(new CommonEvent($user->id));

    $this->unit->whereIn('id', $ids)->delete();
    return back()->with('message', 'Unit deleted');
  }

  // function call for dropdown. it needs SelectInfinite component. It is send json response
  public function unitDropdown(Request $request)
  {
    $search = trim((string) $request->input('search', ''));
    $termLike   = $search !== '' ? "%$search%" : null;

    $unit = $this->unit->query()
      ->when($search !== '', function ($query) use ($termLike) {
        $query->where('name', 'ilike', $termLike);
      })
      ->orderBy('id', 'desc')
      ->paginate((int)request('size') ? (int)request('size') : 5);
    return response()->json(['unit' => $unit]);
  }
}
