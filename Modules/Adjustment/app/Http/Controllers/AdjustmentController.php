<?php

namespace Modules\Adjustment\Http\Controllers;

use App\Events\CommonEvent;
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
    $isAdmin = in_array(optional($user->role)->name, ['admin']);
    $search = $request->input('search');

    $query = $this->adjustment->with(['client:id,bin,company_name', 'expense:id,name'])
      ->when(!$isAdmin, fn($query) => $query->whereHas('client', fn($q) => $q->where('user_id', $user->id)))
      ->when($search, function ($query) use ($search) {
        $query->where(function ($query) use ($search) {
          $query->where(DB::raw('tax_period'), 'like', '%' . strtolower($search) . '%')
            ->orWhereHas('expense', fn($query) => $query->where('name', 'like', '%' . strtolower($search) . '%'))
            ->orWhereHas('client', function ($query) use ($search) {
              $query->where(function ($query) use ($search) {
                $query->where('bin', 'like', '%' . strtolower($search) . '%')
                  ->orWhere('company_name', 'like', '%' . strtolower($search) . '%');
              });
            });
        });
      })->orderBy('id', 'desc')
      ->paginate((int)request('size', 10))
      ->withQueryString();

    return Inertia("adjustment/index", ['adjustment' => $query, 'search' => $search]);
  }

  // function call from adjustment page for create
  public function store(Request $request)
  {
    $user    = Auth::user();
    $isAdmin = in_array(optional($user->role)->name, ['admin']);

    // Find Closing Balance expense id once
    $closingExpenseId = $this->expense->where('name', 'Closing Balance')->value('id');

    $formField = $request->validate(
      [
        'client_id' => ['required', 'numeric', 'gt:0', function ($attribute, $value, $fail) use ($user, $isAdmin) {
          $client = $this->client->find($value);

          if (!$client) return $fail('Client does not exist');
          if ($client->status == 0) return $fail('BIN inactive');
          if (!$isAdmin && $client->user_id !== $user->id) return $fail('Unauthorised');
        }],
        'expense_id' => ['required', 'numeric', 'gt:0', function ($attribute, $value, $fail) use ($closingExpenseId, $request) {
          $expense = $this->expense->find($value);
          if (!$expense) {
            return $fail('Not exists');
          }
        }],
        'tax_period' => 'required|date_format:"Y-m"|max:7',
        'amount'     => 'required|numeric',
        'remarks'    => 'nullable'
      ],
      [
        'client_id.required' => 'BIN required',
        'client_id.numeric'  => 'Number only',
        'client_id.gt'       => 'Number > 0',

        'expense_id.required' => 'Expense required',
        'expense_id.numeric'  => 'Number only',
        'expense_id.gt'       => 'Number > 0',

        'tax_period.required'    => 'Tax period required',
        'tax_period.date_format' => 'Invalid tax period',
        'tax_period.max'         => 'Max 7 char',

        'amount.required' => 'Amount required',
        'amount.numeric'  => 'Number only',
      ]
    );

    // Insert (Closing Balance is also blocked here if already exists)
    $this->adjustment->create($formField);

    // realtime data
    event(new CommonEvent($user->id));

    return back()->with('message', 'Adjustment created');
  }

  // function call from adjustment page as api for same page
  public function show($id)
  {
    $user = Auth::user();
    $isAdmin = in_array(optional($user->role)->name, ['admin']);

    $adjustment = $this->adjustment->with(['client.division', 'client.user.role', 'expense'])->find($id);

    if (!$isAdmin && (optional($adjustment->client)->user_id !== $user->id)) {
      return response()->json(['error' => 'Unauthorised']);
    }

    return response()->json(['adjustment' => $adjustment]);
  }

  // function call form adjustment page for update
  public function update(Request $request, $id)
  {
    $user = Auth::user();
    $isAdmin = in_array(optional($user->role)->name, ['admin']);

    $formField = $request->validate(
      [
        'client_id' => ['required', 'numeric', 'gt:0', function ($attribute, $value, $fail) use ($user, $isAdmin) {
          $client = $this->client->find($value);

          if (!$client) return $fail('Client does not exist');

          if ($client->status == 0) return $fail('BIN inactive');

          if (!$isAdmin && $client->user_id !== $user->id) return $fail('Unauthorised');
        }],
        'expense_id' => ['required', 'numeric', 'gt:0', function ($attribute, $value, $fail) {
          $expense = $this->expense->find($value);
          if (!$expense) return $fail('Not exists');
        }],
        'tax_period' => 'required|date_format:"Y-m"|max:7',
        'amount' => 'required|numeric',
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
      ]
    );

    $this->adjustment->where('id', $id)->update($formField);

    // realtime data
    event(new CommonEvent($user->id));

    return back()->with('message', 'Adjustment updated');
  }

  // function call from adjustment page for delete
  public function destroy($id)
  {
    $user    = Auth::user();
    $isAdmin = in_array(optional($user->role)->name, ['admin']);
    $ids     = explode(',', $id);

    // Load adjustments with client + expense
    $adjustment = $this->adjustment
      ->with(['client', 'expense'])
      ->whereIn('id', $ids)
      ->get();

    if ($adjustment->isEmpty()) {
      return back()->with('error', 'No data found');
    }

    // Authorisation for non-admin
    if (!$isAdmin) {
      $permission = $adjustment->filter(function ($row) use ($user) {
        return $row->client?->user_id !== $user->id;
      });

      if ($permission->isNotEmpty()) {
        return back()->with('error', 'Unauthorised.');
      }
    }

    // Now safe to delete
    $deleted = $this->adjustment->whereIn('id', $ids)->delete();

    // realtime data
    event(new CommonEvent($user->id));

    return back()->with('message', "Adjustment deleted ({$deleted})");
  }

  // function call for dropdown. it needs SelectInfinite component. It is send json response
  public function adjustmentDropdown(Request $request)
  {
    $user = Auth::user();
    $isAdmin = in_array(optional($user->role)->name, ['admin']);
    $search = $request->input('search');

    $query = $this->adjustment->with(['client', 'expense'])
      ->when(!$isAdmin, fn($q) => $q->whereHas('client', fn($query) => $query->where('user_id', $user->id)))
      ->when($search, function ($query) use ($search) {
        $query->orWhereHas('expense', fn($query) => $query->where('name', 'like', '%' . strtolower($search) . '%'));
      })
      ->orderBy('id', 'desc')
      ->paginate((int)request('size', 10));

    return response()->json(['adjustment' => $query]);
  }
}
