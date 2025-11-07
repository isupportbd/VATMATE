<?php

namespace Modules\Adjustment\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Adjustment\Models\Adjustment;
use Modules\Client\Models\Client;
use Modules\Expense\Models\Expense;

class AdjustmentController extends Controller
{
  public $adjustment;
  public $client;
  public $expense;
  public function __construct(Adjustment $adjustment, Client $client, Expense $expense)
  {
    $this->adjustment = $adjustment;
    $this->client = $client;
    $this->expense = $expense;
  }

  // function call automatically from adjustment page
  public function index(Request $request)
  {
    $user = Auth::user();
    $isAdmin = in_array($user->role->name, ['admin']);
    $search = $request->input('search');

    $adjustment = $this->adjustment->with(['client:id,bin,company_name', 'expense:id,name'])
      ->when($search, function ($query) use ($search, $user, $isAdmin) {
        $query->where(function ($query) use ($search, $user, $isAdmin) {
          $query->where(DB::raw('tax_period'), 'like', '%' . strtolower($search) . '%')
            ->orWhereHas('client', function ($query) use ($search, $user, $isAdmin) {
              $query->when(!$isAdmin, fn($query) => $query->where('user_id', $user->id));
              $query->where(function ($query) use ($search) {
                $query->where('bin', 'like', '%' . strtolower($search) . '%')
                  ->orWhere('company_name', 'like', '%' . strtolower($search) . '%');
              });
            })->orWhereHas('expense', fn($query) => $query->where('name', 'like', '%' . strtolower($search) . '%'));
        });
      });

    if (!$isAdmin) {
      $adjustment->whereHas('client', fn($query) => $query->where('user_id', $user->id));
    }

    $result = $adjustment->orderBy('id', 'desc')->paginate((int)request('size', 10))->withQueryString();

    return Inertia("adjustment/index", ['adjustment' => $result, 'search' => $search]);
  }

  // function call from adjustment page for create
  public function store(Request $request)
  {
    $user = Auth::user();
    $isAdmin = in_array($user->role->name, ['admin']);

    $formField = $request->validate(
      [
        'client_id' => ['required', 'numeric', 'gt:0', function ($attribute, $value, $fail) use ($user, $isAdmin) {
          if (!$isAdmin) {
            $userCheck = $this->client->where('id', $value)->where('user_id', $user->id)->exists();
            // error show if role is not admin and client is not under him
            !$userCheck && $fail('Not your client');
          }

          // bin status check
          $bin = $this->client->where('id', $value)->where('status', '0')->first();
          $bin && $fail("BIN inactive");

          // check exists or not
          $binCheck = $this->client->where('id', $value)->exists();
          !$binCheck && $fail('Not exists');
        }],
        'expense_id' => ['required', 'numeric', 'gt:0', function ($attribute, $value, $fail) {
          // check exists or not
          $expenseCheck = $this->expense->where('id', $value)->exists();
          !$expenseCheck && $fail('Not exists');
        }],
        'tax_period' => 'required|date_format:"Y-m"|max:7',
        'amount' => 'required|numeric|gt:0',
        'remarks' => 'nullable'
      ],
      [
        'client_id.required' => 'BIN required',
        'client_id.numeric' => 'Number only',
        'client_id.gt' => 'Number > 0',
        'expense_id.required' => 'Expense required',
        'expense_id.numeric' => 'Number only',
        'expense_id.gt' => 'Number > 0',
        'tax_period.required' =>  'Tax period required',
        'tax_period.date_format' =>  'Invalid tax period',
        'tax_period.max' =>  'Max 7 char',
        'amount.required' =>  'Amount required',
        'amount.numeric' =>  'Number only',
        'amount.gt' =>  'Number > 0',
      ]
    );

    $this->adjustment->create($formField);
    return back()->with('message', 'Adjustment created');
  }

  // function call from adjustment page as api for same page
  public function show($id)
  {
    $user = Auth::user();
    $isAdmin = in_array($user->role->name, ['admin']);

    $adjustment = $this->adjustment->with(['client', 'client.division', 'client.user', 'client.user.role', 'expense']);

    if (!$isAdmin) {
      $adjustment->whereHas('client', fn($query) => $query->where('user_id', $user->id));
    }

    $result = $adjustment->find($id);
    $msg = $result ? ['adjustment' => $result] : ['msg' => 'Nothing found'];
    return response()->json($msg);
  }

  // function call form adjustment page for update
  public function update(Request $request, $id)
  {
    $user = Auth::user();
    $isAdmin = in_array($user->role->name, ['admin']);

    $formField = $request->validate(
      [
        'client_id' => ['required', 'numeric', 'gt:0', function ($attribute, $value, $fail) use ($user, $isAdmin) {
          if (!$isAdmin) {
            $userCheck = $this->client->where('id', $value)->where('user_id', $user->id)->exists();
            // error show if role is not admin and client is not under him
            !$userCheck && $fail('Not your client');
          }

          // bin status check
          $bin = $this->client->where('id', $value)->where('status', '0')->first();
          $bin && $fail("BIN inactive");

          // check exists or not
          $binCheck = $this->client->where('id', $value)->exists();
          !$binCheck && $fail('Not exists');
        }],
        'expense_id' => ['required', 'numeric', 'gt:0', function ($attribute, $value, $fail) {
          // check exists or not
          $expenseCheck = $this->expense->where('id', $value)->exists();
          !$expenseCheck && $fail('Not exists');
        }],
        'tax_period' => 'required|date_format:"Y-m"|max:7',
        'amount' => 'required|numeric|gt:0',
        'remarks' => 'nullable'
      ],
      [
        'client_id.required' => 'BIN required',
        'client_id.numeric' => 'Number only',
        'client_id.gt' => 'Number > 0',
        'expense_id.required' => 'Expense required',
        'expense_id.numeric' => 'Number only',
        'expense_id.gt' => 'Number > 0',
        'tax_period.required' =>  'Tax period required',
        'tax_period.date_format' =>  'Invalid tax period',
        'tax_period.max' =>  'Max 7 char',
        'amount.required' =>  'Amount required',
        'amount.numeric' =>  'Number only',
        'amount.gt' =>  'Number > 0',
      ]
    );
    $result = $this->adjustment->where('id', $id)->update($formField);
    return back()->with('message', 'Adjustment updated');
  }

  // function call from adjustment page for delete
  public function destroy($id)
  {
    $user = Auth::user();
    $isAdmin = in_array($user->role->name, ['admin']);
    $ids = explode(',', $id);

    $adjustment = $this->adjustment->whereIn('id', $ids);

    if (!$isAdmin) {
      $adjustment->whereHas('client', fn($query) => $query->where('user_id', $user->id));
    }

    // Fetch records before delete to check ownership
    $toDelete = $adjustment->get();
    // If no record belongs to user → block deletion
    if ($toDelete->isEmpty()) {
      return back()->with('error', 'Not your client data');
    }

    $adjustment->delete();
    return back()->with('message', 'Adjustment deleted');
  }

  // function call for dropdown. it needs SelectInfinite component. It is send json response
  public function adjustmentDropdown(Request $request)
  {
    $user = Auth::user();
    $isAdmin = in_array($user->role->name, ['admin']);
    $search = $request->input('search');

    $adjustment = $this->adjustment->with(['client', 'expense'])
      ->when($search, function ($query) use ($search, $user, $isAdmin) {
        $query->where(function ($query) use ($search, $user, $isAdmin) {
          $query->when(!$isAdmin, fn($query) => $query->whereHas('client', fn($query) => $query->where('user_id', $user->id)));
          $query->orWhereHas('expense', fn($query) => $query->where('name', 'like', '%' . strtolower($search) . '%'));
        });
      });

    if (!$isAdmin) {
      $adjustment->whereHas('client', fn($query) => $query->where('user_id', $user->id));
    }

    $result = $adjustment->orderBy('id', 'desc')->paginate((int)request('size') ? (int)request('size') : 10);
    return response()->json(['adjustment' => $result]);
  }
}
