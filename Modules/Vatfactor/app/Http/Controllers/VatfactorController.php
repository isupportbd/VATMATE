<?php

namespace Modules\Vatfactor\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
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
    $search = $request->input('search');
    $vatfactor = $this->vatfactor->when($search, function ($query) use ($search) {
      $query->where('name)', 'like', '%' . strtolower($search) . '%');
    })->orderBy('id', 'desc')
      ->paginate((int)request('size', 5))
      ->withQueryString();

    return Inertia("vatfactor/index", ['vatfactor' => $vatfactor, 'search' => $search]);
  }

  // function call from vatfactor page as api for same page
  public function show($id)
  {
    $result = $this->vatfactor->find($id);
    $msg = $result ? ['vatfactor' => $result] : ['msg' => 'Nothing found'];
    return response()->json($msg);
    // return inertia("item/index", ['single' => $result]);
  }

  // function call form vcatfactor page for update
  public function update(Request $request, $id)
  {
    $formField = $request->validate(
      [
        'name' => 'required|max:20',
        'value' => 'required|numeric|gt:0'
      ],
      [
        'name.required' => 'Name required',
        'name.max' => 'Max 20 char',
        'value.required' => 'Value required',
        'value.numeric' => 'Number only',
        'value.gt' => 'Number > 0',
      ]
    );

    $this->vatfactor->where('id', $id)->update($formField);
    return back()->with('message', 'Vat factor updated');
  }

  // function call for dropdown. it needs SelectInfinite component. It is send json response
  public function vatfactorDropdown()
  {
    $vatfactor = $this->vatfactor->when(request('search'), function ($query, $search) {
      $query->where('name', 'like', '%' . strtolower($search) . '%');
    })->orderBy('id', 'desc')
      ->paginate((int)request('size') ? (int)request('size') : 5);

    return response()->json(['vatfactor' => $vatfactor]);
  }
}
