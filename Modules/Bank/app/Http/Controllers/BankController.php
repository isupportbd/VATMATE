<?php

namespace Modules\Bank\Http\Controllers;

use App\Events\CommonEvent;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Bank\Models\Bank;

class BankController extends Controller
{
  public $bank;
  public function __construct(Bank $bank)
  {
    $this->bank = $bank;
  }

  // function call automatically from bank page
  public function index(Request $request)
  {
    $search = trim((string) $request->input('search', ''));
    $termLike   = $search !== '' ? "%$search%" : null;

    $bank = $this->bank->when($search !== '', fn($query) => $query->where('name', 'ilike', $termLike))
      ->orderBy('id', 'desc')
      ->paginate((int)request('size', 10))
      ->withQueryString();

    return Inertia("bank/index", ['bank' => $bank, 'search' => $search]);
  }

  // function call from bank for create
  public function store(Request $request)
  {
    $user = Auth::user();

    $formField = $request->validate(
      ['name' => 'required|unique:banks,name|max:200'],
      [
        'name.required' => 'Bank required',
        'name.unique' => 'Exists, change your input',
        'name.max' => 'Max 200 char'
      ]
    );

    $this->bank->create($formField);

    // realtime data
    event(new CommonEvent($user->id));

    return back()->with('message', 'Bank created');
  }

  // function call from bank page as api for same page
  public function show($id)
  {
    $result = $this->bank->find($id);
    $msg = $result ? ['bank' => $result] : ['msg' => 'Nothing found'];
    return response()->json($msg);
    // return inertia("bank/index", ['single' => $result]);
  }

  // function call form bank page for update
  public function update(Request $request, $id)
  {
    $user = Auth::user();

    $formField = $request->validate(
      ['name' => 'required|unique:banks,name|max:200'],
      [
        'name.required' =>  'Bank required',
        'name.unique' => 'Exists, change your input',
        'name.max' => 'Max 200 char'
      ]
    );

    $this->bank->where('id', $id)->update($formField);

    // realtime data
    event(new CommonEvent($user->id));

    return back()->with('message', 'Bank updated');
  }

  // function call from bank page for delete
  public function destroy($id)
  {
    $user = Auth::user();

    $ids = explode(',', $id);

    $this->bank->whereIn('id', $ids)->delete();

    // realtime data
    event(new CommonEvent($user->id));

    return back()->with('message', 'Bank deleted');
  }

  // function call for dropdown. it needs SelectInfinite component. It is send json response
  public function bankDropdown(Request $request)
  {
    $search = trim((string) $request->input('search', ''));
    $termLike   = $search !== '' ? "%$search%" : null;

    $bank = $this->bank->when($search !== '', fn($query) => $query->where('name', 'ilike', $termLike))
      ->orderBy('id', 'desc')
      ->paginate((int)request('size') ? (int)request('size') : 5);

    return response()->json(['bank' => $bank]);
  }
}
