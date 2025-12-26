<?php

namespace Modules\Stationcode\Http\Controllers;

use App\Events\CommonEvent;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Stationcode\Models\Stationcode;

class StationcodeController extends Controller
{
  public $stationcode;
  public function __construct(Stationcode $stationcode)
  {
    $this->stationcode = $stationcode;
  }

  // function call automatically from stationcode page
  public function index(Request $request)
  {
    $search = trim((string) $request->input('search', ''));
    $termLike   = $search !== '' ? "%$search%" : null;

    $stationcode = $this->stationcode->when($search !== '', fn($query) => $query->where('code', 'ilike', $termLike))
      ->orderBy('id', 'desc')
      ->paginate((int)request('size', 10))
      ->withQueryString();

    return Inertia("stationcode/index", ['stationcode' => $stationcode, 'search' => $search]);
  }

  // function call from statiocode page for create
  public function store(Request $request)
  {
    $user = Auth::user();

    $formField = $request->validate(
      ['code' => 'required|unique:stationcodes,code|max:10'],
      [
        'code.required' => 'Stationcode required',
        'code.unique' => 'Exists, change your input',
        'code.max' => 'Max 10 char',
      ]
    );
    $this->stationcode->create($formField);

    // realtime data
    event(new CommonEvent($user->id));

    return back()->with('message', 'Stationcode created');
  }

  // function call from stationcode page as api for same page
  public function show($id)
  {
    $result = $this->stationcode->find($id);
    $msg = $result ? ['stationcode' => $result] : ['msg' => 'Nothing found'];
    return response()->json($msg);
    // return inertia("item/index", ['single' => $result]);
  }

  // function call form stationcode page for update
  public function update(Request $request, $id)
  {
    $user = Auth::user();

    $formField = $request->validate(
      ['code' => 'required|unique:stationcodes,code|max:10'],
      [
        'code.required' =>  'Stationcode required',
        'code.unique' => 'Exists, change your input',
        'code.max' => 'Max 10 char',
      ]
    );
    $this->stationcode->where('id', $id)->update($formField);

    // realtime data
    event(new CommonEvent($user->id));

    return back()->with('message', 'Stationcode updated');
  }

  // function call from stationcode page for delete
  public function destroy($id)
  {
    $user = Auth::user();

    $ids = explode(',', $id);

    // realtime data
    event(new CommonEvent($user->id));

    $this->stationcode->whereIn('id', $ids)->delete();
    return back()->with('message', 'Stationcode deleted');
  }

  // function call for dropdown. it needs SelectInfinite component. It is send json response
  public function stationcodeDropdown(Request $request)
  {
    $search = trim((string) $request->input('search', ''));
    $termLike   = $search !== '' ? "%$search%" : null;

    $stationcode = $this->stationcode->query()
      ->when($search !== '', function ($query) use ($termLike) {
        $query->where('code', 'ilike', $termLike);
      })
      ->orderBy('id', 'desc')
      ->paginate((int)request('size') ? (int)request('size') : 5);

    return response()->json(['stationcode' => $stationcode]);
  }
}
