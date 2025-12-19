<?php

namespace Modules\Item\Http\Controllers;

use App\Events\CommonEvent;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Item\Models\Item;

class ItemController extends Controller
{
  public $item;
  public function __construct(Item $item)
  {
    $this->item = $item;
  }

  // function automatically from item page
  public function index(Request $request)
  {
    $search = trim((string) $request->input('search', ''));
    $termLike   = $search !== '' ? $search . '%' : null;

    $item = $this->item->when($search, function ($query) use ($termLike) {
      $query->where(DB::raw('lower(name)'), 'like', $termLike)
        ->orWhere(DB::raw('lower(hs_code)'), 'like', $termLike);
    })->orderBy('id', 'desc')
      ->paginate((int)request('size', 10))
      ->withQueryString();

    return Inertia("item/index", ['item' => $item, 'search' => $search]);
  }

  // function call from item page for create
  public function store(Request $request)
  {
    $user = Auth::user();

    $formField = $request->validate(
      [
        'name' => 'required|max:250',
        'hs_code' => ['required', 'max:10', function ($attribute, $value, $fail) use ($request) {
          $check_unique = $this->item->where([['name', $request->input('name')], ['hs_code', $value]])->exists();

          if ($check_unique) return $fail('Exists, change your input');
        }],
        'description' => 'nullable|max:250',
        'cd' => 'required|numeric',
        'rd' => 'required|numeric',
        'sd' => 'required|numeric',
        'vat' => 'required|numeric',
        'at' => 'required|numeric',
      ],
      [
        'name.required' => 'Name required',
        'name.unique' => 'Exists, change your input',
        'name.max' => 'Max 150 char',
        'hs_code.required' => 'HS code required',
        'hs_code.unique' => 'Exists, change your input',
        'hs_code.max' => 'Max 10 char',
        'description.max' => 'Max 250 char',
        'cd.required' => 'CD required',
        'cd.numeric' => 'Number only',
        'rd.required' => 'RD required',
        'rd.numeric' => 'Number only',
        'sd.required' => 'SD required',
        'sd.numeric' => 'Number only',
        'vat.required' => 'VAT required',
        'vat.numeric' => 'Number only',
        'at.required' => 'AT required',
        'at.numeric' => 'Number only',
      ]
    );
    $this->item->create($formField);

    // realtime data
    event(new CommonEvent($user->id));

    return back()->with('message', 'Item created');

    // $dd = $this->item->where([['name', $request->input('name')], ['hs_code', $request->input('hs_code')]])->exists();
    // dd($dd);
  }

  // function call from item page as api for same page
  public function show($id)
  {
    $result = $this->item->find($id);
    $msg = $result ? ['item' => $result] : ['msg' => 'Nothing found'];
    return response()->json($msg);
    // return inertia("item/index", ['single' => $result]);
  }

  // function call form item page for update
  public function update(Request $request, $id)
  {
    $user = Auth::user();

    $formField = $request->validate(
      [
        'name' => 'required|max:250',
        'hs_code' => ['required', 'max:10', function ($attribute, $value, $fail) use ($request) {
          $check_unique = $this->item->where([['name', $request->input('name')], ['hs_code', $value]])->exists();

          if ($check_unique) return $fail('Exists, change your input');
        }],
        'description' => 'nullable|max:250',
        'cd' => 'required|numeric',
        'rd' => 'required|numeric',
        'sd' => 'required|numeric',
        'vat' => 'required|numeric',
        'at' => 'required|numeric',
      ],
      [
        'name.required' => 'Name required',
        'name.unique' => 'Exists, change your input',
        'name.max' => 'Max 150 char',
        'hs_code.required' => 'HS code required',
        'hs_code.unique' => 'Exists, change your input',
        'hs_code.max' => 'Max 10 char',
        'description.max' => 'Max 250 char',
        'cd.required' => 'CD required',
        'cd.numeric' => 'Number only',
        'rd.required' => 'RD required',
        'rd.numeric' => 'Number only',
        'sd.required' => 'SD required',
        'sd.numeric' => 'Number only',
        'vat.required' => 'VAT required',
        'vat.numeric' => 'Number only',
        'at.required' => 'AT required',
        'at.numeric' => 'Number only',
      ]
    );
    $this->item->where('id', $id)->update($formField);

    // realtime data
    event(new CommonEvent($user->id));

    return back()->with('message', 'Item updated');
  }

  // function call from item page for delete
  public function destroy($id)
  {
    $user = Auth::user();

    $ids = explode(',', $id);

    $this->item->whereIn('id', $ids)->delete();

    // realtime data
    event(new CommonEvent($user->id));

    return back()->with('message', 'Item deleted');
  }

  // function call for dropdown. it needs SelectInfinite component. It is send json response
  public function itemDropdown(Request $request)
  {
    $search = trim((string) $request->input('search', ''));
    $termLike   = $search !== '' ? $search . '%' : null;

    $item = $this->item->query()
      ->when($search, function ($query) use ($termLike) {
        $query->where(DB::raw('lower(name)'), 'like', $termLike)
          ->orWhere(DB::raw('lower(hs_code)'), 'like', $termLike);
      })
      ->orderBy('id', 'desc')
      ->paginate((int)request('size') ? (int)request('size') : 10);

    return response()->json(['item' => $item]);
  }
}
