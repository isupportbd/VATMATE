<?php

namespace Modules\Item\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
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
    $search = $request->input('search');
    $item = $this->item->when($search, function ($query) use ($search) {
      $query->where(DB::raw('lower(name)'), 'like', '%' . strtolower($search) . '%')
        ->orWhere(DB::raw('lower(hs_code)'), 'like', '%' . strtolower($search) . '%');
    })->orderBy('id', 'desc')
      ->paginate((int)request('size') ? (int)request('size') : 10)
      ->withQueryString();

    return Inertia("item/index", ['item' => $item, 'search' => $search]);
  }

  // function call from item page for create
  public function store(Request $request)
  {
    $formField = $request->validate(
      [
        'name' => 'required|max:250',
        'hs_code' => ['required', 'max:20', function ($attribute, $value, $fail) use ($request) {
          $check_unique = $this->item->where([['name', $request->input('name')], ['hs_code', $value]])->exists();

          $check_unique && $fail('Exists, change your input');
        }],
        'description' => 'nullable|max:250',
        'cd' => 'required|numeric',
        'rd' => 'required|numeric',
        'sd' => 'required|numeric',
        'vat' => 'required|numeric',
        'ait' => 'required|numeric',
        'at' => 'required|numeric',
      ],
      [
        'name.required' => 'Name required',
        'name.unique' => 'Exists, change your input',
        'name.max' => 'Max 150 char',
        'hs_code.required' => 'HS code required',
        'hs_code.unique' => 'Exists, change your input',
        'hs_code.max' => 'Max 20 char',
        'description.max' => 'Max 250 char',
        'cd.required' => 'CD required',
        'cd.numeric' => 'Number only',
        'rd.required' => 'RD required',
        'rd.numeric' => 'Number only',
        'sd.required' => 'SD required',
        'sd.numeric' => 'Number only',
        'vat.required' => 'VAT required',
        'vat.numeric' => 'Number only',
        'ait.required' => 'AIT required',
        'ait.numeric' => 'Number only',
        'at.required' => 'AT required',
        'at.numeric' => 'Number only',
      ]
    );
    $this->item->create($formField);
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
    $formField = $request->validate(
      [
        'name' => 'required|max:250',
        'hs_code' => ['required', 'max:20', function ($attribute, $value, $fail) use ($request) {
          $check_unique = $this->item->where([['name', $request->input('name')], ['hs_code', $value]])->exists();

          $check_unique && $fail('Exists, change your input');
        }],
        'description' => 'nullable|max:250',
        'cd' => 'required|numeric',
        'rd' => 'required|numeric',
        'sd' => 'required|numeric',
        'vat' => 'required|numeric',
        'ait' => 'required|numeric',
        'at' => 'required|numeric',
      ],
      [
        'name.required' => 'Name required',
        'name.unique' => 'Exists, change your input',
        'name.max' => 'Max 150 char',
        'hs_code.required' => 'HS code required',
        'hs_code.unique' => 'Exists, change your input',
        'hs_code.max' => 'Max 20 char',
        'description.max' => 'Max 250 char',
        'cd.required' => 'CD required',
        'cd.numeric' => 'Number only',
        'rd.required' => 'RD required',
        'rd.numeric' => 'Number only',
        'sd.required' => 'SD required',
        'sd.numeric' => 'Number only',
        'vat.required' => 'VAT required',
        'vat.numeric' => 'Number only',
        'ait.required' => 'AIT required',
        'ait.numeric' => 'Number only',
        'at.required' => 'AT required',
        'at.numeric' => 'Number only',
      ]
    );
    $this->item->where('id', $id)->update($formField);
    return back()->with('message', 'Item updated');
  }

  // function call from item page for delete
  public function destroy($id)
  {
    $ids = explode(',', $id);
    $this->item->whereIn('id', $ids)->delete();
    return back()->with('message', 'Item deleted');
  }

  // function call for dropdown. it needs SelectInfinite component. It is send json response
  public function itemDropdown()
  {
    $item = $this->item->query()
      ->when(request('search'), function ($query, $search) {
        $query->where(DB::raw('lower(name)'), 'like', '%' . strtolower($search) . '%')
          ->orWhere(DB::raw('lower(hs_code)'), 'like', '%' . strtolower($search) . '%');
      })
      ->orderBy('id', 'desc')
      ->paginate((int)request('size') ? (int)request('size') : 10);

    return response()->json(['item' => $item]);
  }
}
