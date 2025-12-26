<?php

namespace Modules\Branch\Http\Controllers;

use App\Events\CommonEvent;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Bank\Models\Bank;
use Modules\Branch\Models\Branch;
use Modules\Division\Models\Division;

class BranchController extends Controller
{
  public $bank;
  public $branch;
  public $division;
  public function __construct(Bank $bank, Branch $branch, Division $division)
  {
    $this->bank = $bank;
    $this->branch = $branch;
    $this->division = $division;
  }

  // function call automatically from branch page
  public function index(Request $request)
  {
    $search = trim((string) $request->input('search', ''));
    $termLike   = $search !== '' ? "%$search%" : null;

    $branch = $this->branch->with(['division', 'bank'])
      ->when($search !== '', function ($query) use ($termLike) {
        $query->where('name', 'ilike', $termLike)
          ->orWhereHas('division', fn($query) => $query->where('name', 'ilike', $termLike))
          ->orWhereHas('bank', fn($query) => $query->where('name', 'ilike', $termLike));
      })
      ->orderBy('id', 'desc')
      ->paginate((int)request('size', 10))
      ->withQueryString();

    return Inertia("branch/index", ['branch' => $branch, 'search' => $search]);
  }

  // function call from branch page form create
  public function store(Request $request)
  {
    $user = Auth::user();

    $formField = $request->validate(
      [
        'division_id' => ['required', 'numeric', 'gt:0', function ($attribute, $value, $fail) {
          // check exists or not
          $divisionCheck = $this->division->where('id', $value)->exists();
          if (!$divisionCheck) return $fail('Not exists');
        }],
        'bank_id' => ['required', 'numeric', 'gt:0', function ($attribute, $value, $fail) {
          // check exists or not
          $bankCheck = $this->bank->where('id', $value)->exists();
          if (!$bankCheck) return $fail('Not exists');
        }],
        'name' => [
          'required',
          'max:200',
          function ($attribute, $value, $fail) use ($request) {
            $result = $this->branch->where([['division_id', $request->input('division_id')], ['bank_id', $request->input('bank_id')], ['name', $value]])->exists();

            if ($result) return $fail('Exists, change your input');
          }
        ]
      ],
      [
        'division_id.required' => 'Division required',
        'division_id.numeric' => 'Number only',
        'division_id.gt' => 'Number > 0',
        'bank_id.required' => 'Bank required',
        'bank_id.numeric' => 'Number only',
        'bank_id.gt' => 'Number > 0',
        'name.required' =>  'Branch required',
        'name.max' =>  'Max 200 char'
      ]
    );
    $this->branch->create($formField);

    // realtime data
    event(new CommonEvent($user->id));

    return back()->with('message', 'Branch created');
  }

  // function call from branch page for show
  public function show($id)
  {
    $result = $this->branch->with(['division', 'bank'])->find($id);
    $msg = $result ? ['branch' => $result] : ['msg' => 'Nothing found'];
    return response()->json($msg);
    // return inertia("branch/index", ['single' => $result]);
  }

  // function call from branch page for update
  public function update(Request $request, $id)
  {
    $user = Auth::user();

    $formField = $request->validate(
      [
        'division_id' => ['required', 'numeric', 'gt:0', function ($attribute, $value, $fail) {
          // check exists or not
          $divisionCheck = $this->division->where('id', $value)->exists();
          if (!$divisionCheck) return $fail('Not exists');
        }],
        'bank_id' => ['required', 'numeric', 'gt:0', function ($attribute, $value, $fail) {
          // check exists or not
          $bankCheck = $this->bank->where('id', $value)->exists();
          if (!$bankCheck) return $fail('Not exists');
        }],
        'name' => [
          'required',
          'max:200',
          function ($attribute, $value, $fail) use ($request) {
            $result = $this->branch->where([['division_id', $request->input('division_id')], ['bank_id', $request->input('bank_id')], ['name', $value]])->exists();

            if ($result) return $fail('Exists, change your input');
          }
        ]
      ],
      [
        'division_id.required' => 'Division required',
        'division_id.numeric' => 'Number only',
        'division_id.gt' => 'Number > 0',
        'bank_id.required' => 'Bank required',
        'bank_id.numeric' => 'Number only',
        'bank_id.gt' => 'Number > 0',
        'name.required' =>  'Branch required',
        'name.max' =>  'Max 200 char',
      ]
    );
    $this->branch->where('id', $id)->update($formField);

    // realtime data
    event(new CommonEvent($user->id));

    return back()->with('message', 'Branch updated');
  }

  // function call from branch page for delete
  public function destroy($id)
  {
    $user = Auth::user();

    $ids = explode(',', $id);

    $this->branch->whereIn('id', $ids)->delete();

    // realtime data
    event(new CommonEvent($user->id));

    return back()->with('message', 'Branch deleted');
  }

  // function call for dropdown. it needs SelectInfinite component. It is send json response
  public function branchDropdown(Request $request)
  {
    $search = trim((string) $request->input('search', ''));
    $termLike   = $search !== '' ? "%$search%" : null;

    $branch = $this->branch->with(['division', 'bank'])
      ->when($search !== '', function ($query) use ($termLike) {
        $query->where('name', 'ilike', $termLike)
          ->orWhereHas('division', fn($query) => $query->where('name', 'ilike', $termLike))
          ->orWhereHas('bank', fn($query) => $query->where('name', 'ilike', $termLike));
      })
      ->orderBy('id', 'desc')
      ->paginate((int)request('size') ? (int)request('size') : 10);

    return response()->json(['branch' => $branch]);
  }
}
