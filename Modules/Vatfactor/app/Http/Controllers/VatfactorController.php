<?php

namespace Modules\Vatfactor\Http\Controllers;

use App\Events\CommonEvent;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Vatfactor\Models\Vatfactor;

class VatfactorController extends Controller
{
  public $vatfactor;
  public function __construct(Vatfactor $vatfactor)
  {
    $this->vatfactor = $vatfactor;
  }

  // function call automatically from vatfactor page
  public function index(Request $request)
  {
    $search = trim((string) $request->input('search', ''));
    $termLike   = $search !== '' ? "%$search%" : null;

    $vatfactor = $this->vatfactor->when($search !== '', function ($query) use ($termLike) {
      $query->where('name', 'ilike', $termLike);
    })->orderBy('id', 'desc')
      ->paginate((int)request('size', 5))
      ->withQueryString();

    return Inertia("vatfactor/index", ['vatfactor' => $vatfactor, 'search' => $search]);
  }

  // function call from vatfactor page as api for same page
  public function show($id)
  {
    $result = $this->vatfactor->find($id);
    return response()->json(['vatfactor' => $result]);
    // return Inertia("item/index", ['single' => $result]);
  }

  // function call form vcatfactor page for update
  public function update(Request $request, $id)
  {
    $user = Auth::user();

    $formField = $request->validate(
      [
        'name' => 'required|max:20',
        'value' => 'required|numeric|gt:0',
        'description' => 'required|max:150',
      ],
      [
        'name.required' => 'Name required',
        'name.max' => 'Max 20 char',
        'value.required' => 'Value required',
        'value.numeric' => 'Number only',
        'value.gt' => 'Number > 0',
        'description.required' => 'Description',
        'description.max' => 'Max 150 char',
      ]
    );

    unset($formField['name']);
    $this->vatfactor->where('id', $id)->update($formField);

    // realtime data
    event(new CommonEvent($user->id));

    return back()->with('message', 'Vat factor updated');
  }

  // function call for dropdown. it needs SelectInfinite component. It is send json response
  public function vatfactorDropdown(Request $request)
  {
    $search = trim((string) $request->input('search', ''));
    $termLike   = $search !== '' ? "%$search%" : null;

    $vatfactor = $this->vatfactor->when(request('search'), function ($query) use ($termLike) {
      $query->where('name', 'ilike', $termLike);
    })->orderBy('id', 'desc')
      ->paginate((int)request('size') ? (int)request('size') : 5);

    return response()->json(['vatfactor' => $vatfactor]);
  }
}
