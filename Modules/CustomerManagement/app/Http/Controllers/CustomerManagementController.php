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
    $isAdmin = in_array($user->role->name, ['admin']);
    $search = $request->input('search');

    $customer = $this->customer->with(['client'])
      ->when($search, function ($query) use ($search, $user, $isAdmin) {
        $query->where(function ($query) use ($search, $user, $isAdmin) {
          $query->where('mobile', 'like', '%' . strtolower($search) . '%')
            ->orWhereHas('client', function ($query) use ($search, $user, $isAdmin) {
              $query->when(!$isAdmin, fn($query) => $query->where('user_id', $user->id));
              $query->where(function ($query) use ($search) {
                $query->where('email', 'like', '%' . strtolower($search) . '%')
                  ->orWhere('bin', 'like', '%' . strtolower($search) . '%')
                  ->orWhere('mobile', 'like', '%' . strtolower($search) . '%')
                  ->orWhere('company_name', 'like', '%' . strtolower($search) . '%')
                  ->orWhereHas('division', fn($query) => $query->where('name', 'like', '%' . strtolower($search) . '%'))
                  ->orWHereHas('user', fn($query) => $query->where('email', 'like', '%' . strtolower($search) . '%'));
              });
            });
        });
      });

    if (!$isAdmin) {
      $customer->whereHas('client', fn($query) => $query->where('user_id', $user->id));
    }

    $result = $customer->orderBy('id', 'desc')->paginate((int)request('size', 10))->withQueryString();

    return Inertia("customer/index", ['customer' => $result, 'search' => $search]);
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
        'mobile' => 'required|unique:customer_management,mobile|max:14',
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
        'mobile.max' => 'Max 14 char',
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
    $isAdmin = in_array($user->role->name, ['admin']);

    $customer = $this->customer->with(['client']);

    if (!$isAdmin) {
      $customer->whereHas('client', fn($query) => $query->where('user_id', $user->id));
    }

    $result = $customer->find($id);
    $msg = $result ? ['customer' => $result] : ['msg' => 'Nothing found'];
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
        'mobile' => 'required|unique:customer_management,mobile|max:14',
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
        'mobile.max' => 'Max 14 char',
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
    $isAdmin = in_array($user->role->name, ['admin']);
    $ids = explode(',', $id);

    $customer = $this->customer->whereIn('id', $ids);

    if (!$isAdmin) {
      $customer->whereHas('client', fn($query) => $query->where('user_id', $user->id));
    }

    // Fetch records before delete to check ownership
    $toDelete = $customer->get();
    // If no record belongs to user → block deletion
    if ($toDelete->isEmpty()) {
      return back()->with('error', 'Not your client data');
    }

    $customer->delete();
    return back()->with('message', 'Customer deleted');
  }

  // function call for dropdown. it needs SelectInfinite component. It is send json response
  public function customerDropdown(Request $request)
  {
    $user = Auth::user();
    $isAdmin = in_array($user->role->name, ['admin']);
    $search = $request->input('search');

    $customer = $this->customer->with(['client'])->when($search, function ($query) use ($search) {
      $query->where('mobile', 'like', '%' . strtolower($search) . '%');
    });

    if (!$isAdmin) {
      $customer->whereHas('client', fn($query) => $query->where('user_id', $user->id));
    }

    $result = $customer->orderBy('id', 'desc')->paginate((int)request('size') ? (int)request('size') : 10);
    return response()->json(['customer' => $result]);
  }
}
