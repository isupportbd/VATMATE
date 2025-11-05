<?php

namespace Modules\Treasury\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Branch\Models\Branch;
use Modules\Client\Models\Client;
use Modules\Treasury\Models\Treasury;

class TreasuryController extends Controller
{
  public $branch;
  public $client;
  public $treasury;
  public function __construct(Branch $branch, Client $client, Treasury $treasury)
  {
    $this->branch = $branch;
    $this->client = $client;
    $this->treasury = $treasury;
  }

  // function call automatically from treasury page
  public function index(Request $request)
  {
    $user = Auth::user();
    $search = $request->input('search');

    $treasury = $this->treasury->with(['client', 'client.division', 'client.user', 'client.user.role', 'branch', 'branch.bank', 'branch.division'])
      ->when($search, function ($query) use ($search, $user) {
        $query->where('tax_period', 'like', '%' . strtolower($search) . '%')
          ->orWhereHas('client', function ($query) use ($search, $user) {
            if (!in_array($user->role->name, ['admin'])) {
              $query->where('user_id', $user->id)->where(function ($query) use ($search) {
                $query->where('email', 'like', '%' . strtolower($search) . '%')
                  ->orWhere('mobile', 'like', '%' . strtolower($search) . '%')
                  ->orWhere('company_name', 'like', '%' . strtolower($search) . '%')
                  ->orWhere('reference', 'like', '%' . strtolower($search) . '%')
                  ->orWhere('bin', 'like', '%' . strtolower($search) . '%');
              });
            } else {
              $query->where('email', 'like', '%' . strtolower($search) . '%')
                ->orWhere('bin', 'like', '%' . strtolower($search) . '%')
                ->orWhere('mobile', 'like', '%' . strtolower($search) . '%')
                ->orWhere('company_name', 'like', '%' . strtolower($search) . '%')
                ->orWhere('reference', 'like', '%' . strtolower($search) . '%');
            }
          })
          ->orWHereHas('client.user', fn($query) => $query->where('email', 'like', '%' . strtolower($search) . '%'))
          ->orWhereHas('branch', fn($query) => $query->where('name', 'like', '%' . strtolower($search) . '%'))
          ->orWhereHas('branch.bank', fn($query) => $query->where('name', 'like', '%' . strtolower($search) . '%'))
          ->orWhereHas('branch.division', fn($query) => $query->where('name', 'like', '%' . strtolower($search) . '%'));
      });

    if (!in_array($user->role->name, ['admin'])) {
      $treasury->whereHas('client', fn($query) => $query->where('user_id', $user->id));
    }

    $result = $treasury->orderBy('id', 'desc')
      ->paginate((int)request('size') ? (int)request('size') : 10)
      ->withQueryString();

    return Inertia("treasury/index", ['treasury' => $result, 'search' => $search]);
  }

  // function call from treasuty page for create
  public function store(Request $request)
  {
    $user = Auth::user();
    $formField = $request->validate(
      [
        'client_id' => ['required', 'numeric', 'gt:0', function ($attribute, $value, $fail) use ($user) {
          if (!in_array($user->role->name, ['admin'])) {
            $userCheck = $this->client->where('id', $value)->where('user_id', $user->id)->exists();
            // error show if role is not admin and client is not under him
            !$userCheck && $fail('Not your client');
          }

          // bin status check
          $clientbin = $this->client->where('id', $value)->where('status', '0')->first();
          $clientbin && $fail("BIN inactive");

          // check exists or not
          $binCheck = $this->client->where('id', $value)->exists();
          !$binCheck && $fail('Not exists');
        }],
        'branch_id' => ['required', 'numeric', 'gt:0', function ($attribute, $value, $fail) {
          // check exists or not
          $branchCheck = $this->branch->where('id', $value)->exists();
          !$branchCheck && $fail('Not exists');
        }],
        'tax_period' => 'required|date_format:"Y-m"',
        'challan_no' => 'required|max:16',
        'amount' => 'required|numeric|gt:0',
        'remarks' => 'nullable'
      ],
      [
        'client_id.required' => 'BIN required',
        'client_id.numeric' => 'Number only',
        'client_id.gt' => 'Number > 0',
        'branch_id.required' => 'Branch required',
        'branch_id.numeric' => 'Number only',
        'branch_id.gt' => 'Number > 0',
        'tax_period.required' =>  'Tax period required',
        'tax_period.date_format' =>  'Invalid tax period',
        'challan_no.required' => 'Challan no. required',
        'challan_no.max' => 'Max 16 char',
        'amount.required' =>  'Amount required',
        'amount.numeric' =>  'Number only',
        'amount.gt' =>  'Number > 0',
      ]
    );

    $this->treasury->create($formField);
    return back()->with('message', 'Treasury created');
  }

  // function call from treasury page as api for same page
  public function show($id)
  {
    $user = Auth::user();
    $treasury = $this->treasury->with(['client', 'client.division', 'client.user', 'client.user.role', 'branch', 'branch.bank', 'branch.division']);

    if (!in_array($user->role->name, ['admin'])) {
      $treasury->whereHas('client', fn($query) => $query->where('user_id', $user->id));
    }

    $result = $treasury->find($id);
    $msg = $result ? ['treasury' => $result] : ['msg' => 'Nothing found'];
    return response()->json($msg);
  }

  // function call form treasury page for update
  public function update(Request $request, $id)
  {
    $user = Auth::user();
    $formField = $request->validate(
      [
        'client_id' => ['required', 'numeric', 'gt:0', function ($attribute, $value, $fail) use ($user) {
          if (!in_array($user->role->name, ['admin'])) {
            $userCheck = $this->client->where('id', $value)->where('user_id', $user->id)->exists();
            // error show if role is not admin and client is not under him
            !$userCheck && $fail('Not your client');
          }

          // bin status check
          $clientbin = $this->client->where('id', $value)->where('status', '0')->first();
          $clientbin && $fail("BIN inactive");

          // check exists or not
          $binCheck = $this->client->where('id', $value)->exists();
          !$binCheck && $fail('Not exists');
        }],
        'branch_id' => ['required', 'numeric', 'gt:0', function ($attribute, $value, $fail) {
          // check exists or not
          $branchCheck = $this->branch->where('id', $value)->exists();
          !$branchCheck && $fail('Not exists');
        }],
        'tax_period' => 'required|date_format:"Y-m"|max:7',
        'challan_no' => 'required|max:16',
        'amount' => 'required|numeric|gt:0',
        'remarks' => 'nullable|max:250'
      ],
      [
        'client_id.required' => 'BIN required',
        'client_id.numeric' => 'Number only',
        'client_id.gt' => 'Number > 0',
        'branch_id.required' => 'Branch required',
        'branch_id.numeric' => 'Number only',
        'branch_id.gt' => 'Number > 0',
        'tax_period.required' =>  'Tax period required',
        'tax_period.date_format' =>  'Invalid tax period',
        'tax_period.max' =>  'Max 7 char',
        'challan_no.required' => 'Challan no. required',
        'challan_no.max' => 'Max 16 char',
        'amount.required' =>  'Amount required',
        'amount.numeric' =>  'Number only',
        'amount.gt' =>  'Number > 0',
        'remarks.max' => 'Max 250 char',
      ]
    );
    $this->treasury->where('id', $id)->update($formField);
    return back()->with('message', 'Treasury updated');
  }

  // function call from treasury page for delete
  public function destroy($id)
  {
    $user = Auth::user();
    $ids = explode(',', $id);
    $treasury = $this->treasury->with(['client'])->whereIn('id', $ids);

    if (!in_array($user->role->name, ['admin'])) {
      $treasury->whereHas('client', fn($query) => $query->where('user_id', $user->id));
    }

    $treasury->delete();

    return back()->with('message', 'Treasury deleted');
  }
}
