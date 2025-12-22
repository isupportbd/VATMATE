<?php

namespace Modules\CustomerManagement\Http\Controllers;

use App\Events\CommonEvent;
use App\Http\Controllers\Controller;
use App\Traits\ChecksUserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\CustomerManagement\Models\CustomerManagement;

class CustomerManagementController extends Controller
{
  use ChecksUserRole;

  public $customer;
  public function __construct(CustomerManagement $customer)
  {
    $this->customer = $customer;
  }

  public function index(Request $request)
  {
    $search = trim((string) $request->input('search', ''));
    $termLike   = $search !== '' ? $search . '%' : null;

    $query = $this->customer->query()
      ->when($search !== '', function ($query) use ($termLike) {
        $query->where('mobile', 'like', $termLike)
          ->orWhere('name', 'like', $termLike);
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

    $formField = $request->validate(
      [
        'mobile' => 'required|unique:customer_management,mobile|max:11',
        'customer_name' => 'required|max:100',
        'bin_or_nid' => 'nullable|max:17',
        'address' => 'nullable|max:150',
      ],
      [
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

    // realtime data
    event(new CommonEvent($user->id));

    return back()->with('message', 'Customer created');
  }

  // function call from adjustment page as api for same page
  public function show($id)
  {
    $query = $this->customer->find($id);
    return response()->json(['customer' => $query]);
  }

  // function call form adjustment page for update
  public function update(Request $request, $id)
  {
    $user = Auth::user();

    $formField = $request->validate(
      [
        'mobile' => 'required|unique:customer_management,mobile|max:11',
        'customer_name' => 'required|max:100',
        'bin_or_nid' => 'nullable|max:17',
        'address' => 'nullable|max:150',
      ],
      [
        'mobile.required' => 'Mobile required',
        'mobile.unique' => 'Exists, change your input',
        'mobile.max' => 'Max 11 char',
        'customer.required' => 'Name required',
        'customer.max' => 'Max 100 char',
        'bin_or_nid.max' => 'Max 17 char',
        'address.max' => 'Max 150 char',
      ]
    );

    unset($formField['mobile']);
    $this->customer->where('id', $id)->update($formField);

    // realtime data
    event(new CommonEvent($user->id));

    return back()->with('message', 'Customer updated');
  }

  // function call from adjustment page for delete
  public function destroy($id)
  {
    $user = Auth::user();
    $ids = explode(',', $id);

    // Block immediately if not admin
    if (!$this->hasRole(['admin'])) {
      return back()->with('error', 'Unauthorised');
    }

    $customer = $this->customer->whereIn('id', $ids)->get();

    if ($customer->isEmpty()) {
      return back()->with('error', 'No data found');
    }

    // Admin is allowed — delete now
    $this->customer->whereIn('id', $ids)->delete();

    // realtime data
    event(new CommonEvent($user->id));

    return back()->with('success', 'Deleted successfully');
  }

  // function call for dropdown. it needs SelectInfinite component. It is send json response
  public function customerDropdown(Request $request)
  {
    $search = trim((string) $request->input('search', ''));
    $termLike   = $search !== '' ? $search . '%' : null;

    $query = $this->customer->query()
      ->when($search !== '', function ($query) use ($termLike) {
        $query->where('mobile', 'like', $termLike)
          ->orWhere('name', 'like', $termLike);
      })
      ->orderBy('id', 'desc')
      ->paginate((int)request('size', 10));

    return response()->json(['customer' => $query]);
  }
}
