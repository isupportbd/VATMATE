<?php

namespace Modules\Mushaknote\Http\Controllers;

use App\Events\CommonEvent;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Mushaknote\Models\Mushaknote;

class MushaknoteController extends Controller
{
  public $criteria;
  public $mushaknote;
  public function __construct(Mushaknote $mushaknote)
  {
    $this->mushaknote = $mushaknote;
  }

  // function call automatically from vatfactor page
  public function index(Request $request)
  {
    $search = $request->input('search');
    $mushaknote = $this->mushaknote->when($search, function ($query) use ($search) {
      $query->where('name)', 'like', '%' . strtolower($search) . '%');
    })->orderBy('id', 'desc')
      ->paginate((int)request('size', 5))
      ->withQueryString();

    return Inertia("mushaknote/index", ['mushaknote' => $mushaknote, 'search' => $search]);
  }

  /**
   * Show the specified resource.
   */
  public function show($id)
  {
    $result = $this->mushaknote->find($id);
    $msg = $result ? ['mushaknote' => $result] : ['msg' => 'Nothing found'];

    return response()->json($msg);
  }

  // function call form vcatfactor page for update
  public function update(Request $request, $id)
  {
    $user = Auth::user();

    $formField = $request->validate(
      [
        'name_note_no' => 'required|max:20',
        'percent' => 'required|numeric|gt:0',
      ],
      [
        'name_note_no.required' => 'Name note required',
        'name_note_no.max' => 'Max 20 char',
        'percent.required' => 'Percent required',
        'percent.numeric' => 'Number only',
        'percent.gt' => 'Number > 0',
      ]
    );

    unset($formField['name_note']);
    $this->mushaknote->where('id', $id)->update($formField);

    // realtime data
    event(new CommonEvent($user->id));

    return back()->with('message', 'Note updated');
  }

  // function call from importer puurchase page
  public function mushakDropdown()
  {
    $mushaknote = $this->mushaknote->when(request('search'), function ($query, $search) {
      $query->where('name_note_no', 'like', '%' . strtolower($search) . '%');
    })->orderBy('id', 'asc')
      ->paginate((int)request('size', 5));

    return response()->json(['mushaknote' => $mushaknote]);
  }
}
