<?php

namespace Modules\CustomerManagement\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Client\Models\Client;
use Modules\CustomerManagement\Models\CustomerManagement;

class CustomerManagementController extends Controller
{
  public $client;
  public $customer;
  public function __construct(Client $client, CustomerManagement $customer)
  {
    $this->client = $client;
    $this->customer = $customer;
  }
  public function index(Request $request)
  {
    $user = Auth::user();
    $isAdmin = in_array(optional($user->role)->name, ['admin']);
    $search = $request->input('search');

    $query = $this->customer->with(['client'])
      ->when(!$isAdmin, fn($q) => $q->whereHas('client', fn($query) => $query->where('user_id', $user->id)))
      ->when($search, function ($query) use ($search) {
        $query->where(function ($query) use ($search) {
          $query->whereHas('client', function ($query) use ($search) {
            $query->where(function ($query) use ($search) {
              $query->where('bin', 'like', '%' . strtolower($search) . '%')
                ->orWhere('company_name', 'like', '%' . strtolower($search) . '%');
            });
          })->oWhere('mobile', 'like', '%' . strtolower($search) . '%');
        });
      })
      ->orderBy('id', 'desc')
      ->paginate((int)request('size', 10))
      ->withQueryString();

    return Inertia("customer/index", ['customer' => $query, 'search' => $search]);
  }

  // function call from adjustment page for create
  public function store(Request $request)
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
        'mobile' => 'required|unique:customer_management,mobile|max:11',
        'customer_name' => 'required|max:100',
        'bin_or_nid' => 'nullable|max:17',
        'address' => 'nullable|max:150',
      ],
      [
        'client_id.required' => 'BIN required',
        'client_id.numeric' => 'Number only',
        'client_id.gt' => 'Number > 0',
        'mobile.required' => 'Mobile required',
        'mobile.unique' => 'Exists, change your input',
        'mobile.max' => 'Max 11 char',
        'customer.required' => 'Name required',
        'customer.max' => 'Max 100 char',
        'bin_or_nid.max' => 'Max 17 char',
        'address.max' => 'Max 150 char',
      ]
    );

    $this->customer->create($formField);
    return back()->with('message', 'Customer created');
  }

  // function call from adjustment page as api for same page
  public function show($id)
  {
    $user = Auth::user();
    $isAdmin = in_array(optional($user->role)->name, ['admin']);

    $query = $this->customer->with(['client'])->find($id);

    if (!$isAdmin && $query?->client->user_id !== $user->id) {
      return response()->json(['error' => 'Unauthorised']);
    }

    return response()->json(['customer' => $query]);
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
        'mobile' => 'required|unique:customer_management,mobile|max:11',
        'customer_name' => 'required|max:100',
        'bin_or_nid' => 'nullable|max:17',
        'address' => 'nullable|max:150',
      ],
      [
        'client_id.required' => 'BIN required',
        'client_id.numeric' => 'Number only',
        'client_id.gt' => 'Number > 0',
        'mobile.required' => 'Mobile required',
        'mobile.unique' => 'Exists, change your input',
        'mobile.max' => 'Max 11 char',
        'customer.required' => 'Name required',
        'customer.max' => 'Max 100 char',
        'bin_or_nid.max' => 'Max 17 char',
        'address.max' => 'Max 150 char',
      ]
    );

    $this->customer->where('id', $id)->update($formField);
    return back()->with('message', 'Customer updated');
  }

  // function call from adjustment page for delete
  public function destroy($id)
  {
    $user = Auth::user();
    $isAdmin = in_array(optional($user->role)->name, ['admin']);
    $ids = explode(',', $id);

    $customer = $this->customer->with(['client'])->whereIn('id', $ids)->get();

    if ($customer->isEmpty()) {
      return back()->with('error', 'No data found');
    }

    if (!$isAdmin) {
      $permission = $customer->filter(function ($row) use ($user) {
        return $row->client?->user_id !== $user->id;
      });

      if ($permission->isNotEmpty()) {
        return back()->with('error', 'Unauthorised.');
      }
    }

    // Now safe to delete
    $this->customer->whereIn('id', $ids)->delete();
  }

  // function call for dropdown. it needs SelectInfinite component. It is send json response
  public function customerDropdown(Request $request)
  {
    $user = Auth::user();
    $isAdmin = in_array(optional($user->role)->name, ['admin']);
    $search = $request->input('search');

    $query = $this->customer->with(['client'])
      ->when(!$isAdmin, fn($q) => $q->whereHas('client', fn($query) => $query->where('user_id', $user->id)))
      ->when($search, fn($q) => $q->where('mobile', 'like', '%' . strtolower($search) . '%'))
      ->whereHas('client', fn($q) => $q->where('id', $request->input('client_id')))
      ->orderBy('id', 'desc')
      ->paginate((int)request('size', 10));

    return response()->json(['customer' => $query]);
  }
}
