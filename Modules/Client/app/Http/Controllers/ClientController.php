<?php

namespace Modules\Client\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Client\Models\Client;
use Modules\Division\Models\Division;

class ClientController extends Controller
{
  public $user;
  public $client;
  public $division;
  public $roles;
  public function __construct(User $user, Client $client, Division $division, Role $roles)
  {
    $this->user = $user;
    $this->client = $client;
    $this->division = $division;
    $this->roles = $roles;
  }

  // function automatically call from client page
  public function index(Request $request)
  {
    $user = Auth::user();
    $search = $request->input('search');

    $client = $this->client->with(['division', 'user', 'user.role'])
      ->when($search, function ($query) use ($search, $user) {
        $query->where(function ($query) use ($search, $user) {
          if (!in_array($user->role->name, ['admin'])) {
            $query->where('user_id', $user->id)
              ->where('company_name', 'like', '%' . strtolower($search) . '%')
              ->orWhere('bin', 'like', '%' . strtolower($search) . '%')
              ->orWhere('email', 'like', '%' . strtolower($search) . '%')
              ->orWhere('mobile', 'like', '%' . strtolower($search) . '%')
              ->orWhere('reference', 'like', '%' . strtolower($search) . '%')
              ->orWhereHas('division', fn($query) => $query->where('name', 'like', '%' . strtolower($search) . '%'))
              ->orWhereHas('user', fn($query) => $query->where('email', 'like', '%' . strtolower($search) . '%'))
              ->orWhereHas('user.role', fn($query) => $query->where('name', 'like', '%' . strtolower($search) . '%'));
          } else {
            $query->where('company_name', 'like', '%' . strtolower($search) . '%')
              ->orWhere('email', 'like', '%' . strtolower($search) . '%')
              ->orWhere('bin', 'like', '%' . strtolower($search) . '%')
              ->orWhere('mobile', 'like', '%' . strtolower($search) . '%')
              ->orWhere('reference', 'like', '%' . strtolower($search) . '%')
              ->orWhereHas('division', fn($query) => $query->where('name', 'like', '%' . strtolower($search) . '%'))
              ->orWhereHas('user', fn($query) => $query->where('email', 'like', '%' . strtolower($search) . '%'))
              ->orWhereHas('user.role', fn($query) => $query->where('name', 'like', '%' . strtolower($search) . '%'));
          }
        });
      });

    if (!in_array($user->role->name, ['admin'])) {
      $client->where('user_id', $user->id);
    }

    $result = $client->orderBy('id', 'desc')->paginate((int)request('size') ? (int)request('size') : 10)->withQueryString();

    return Inertia('client/index', ['client' => $result, 'search' => $search]);
  }

  // function call from client page for create
  public function store(Request $request)
  {
    $user = $this->user->find($request->input('user_id'));
    $formField = $request->validate(
      [
        'division_id' => ['required', 'numeric', 'gt:0', function ($attribute, $value, $fail) {
          // check exists or not
          $divisionCheck = $this->division->where('id', $value)->exists();
          !$divisionCheck && $fail('Not exists');
        }],
        'company_name' => 'required|max:200',
        'email' => 'required|email|unique:clients,email|max:200',
        'mobile' => 'required|max:14',
        'address' => 'nullable|max:250',
        'name' => 'nullable|max:100',
        'reference' => 'nullable|max:100',
        'tin' => 'nullable|max:20',
        'circle' => 'nullable|max:50',
        'bin' => 'required|unique:clients,bin',
        'vat_username' => 'required',
        'vat_password' => 'required',
        'user_id' => ['nullable', 'numeric', 'gt:0', function ($attribute, $value, $fail) use ($user) {
          if ($user->role->name == 'superadmin' || $user->role->name == 'admin') {
            $fail('Not assignable to ' . $user->role->name);
          }
        }]
      ],
      [
        'division_id.required' => 'Division required',
        'division_id.numeric' => 'Number only',
        'division_id.gt' => 'Number > 0',
        'company_name.required' => 'Company name required',
        'company_name.max' => 'Max 200 char',
        'email.required' => 'Email required',
        'email.email' => 'Format not valid',
        'email.unique' => 'Exists, change your input',
        'email.max' => 'Max 200 char',
        'mobile.required' => 'Mobile required',
        'mobile.max' => 'Max 14 char',
        'address.max' => 'Max 250 char',
        'name.max' => 'Max 100 char',
        'reference.max' => 'Max 100 char',
        'tin.max' => 'Max 20 char',
        'circle.max' => 'Max 50 char',
        'bin.required' => 'BIN required',
        'bin.unique' => 'Exists, change input',
        'vat_username.required' => 'VAT username required',
        'vat_password.required' => 'VAT password required',
        'user_id.numeric' => 'Number only',
        'user_id.gt' => 'Number > 0',
      ]
    );

    $formField['status'] = '1';
    $this->client->create($formField);
    return back()->with('message', 'Client created');
  }

  // function call from client page for single show
  public function show($id)
  {
    $user = Auth::user();
    $client = $this->client->with(['division', 'user', 'user.role']);

    if (!in_array($user->role->name, ['admin'])) {
      $client->where('user_id', $user->id);
    }

    $result = $client->find($id);
    $msg = $result ? ['client' => $result] : ['msg' => 'Nothing found'];
    return response()->json($msg);
  }

  // function call from client page for update
  public function update(Request $request, $id)
  {
    $user = $this->user->find($request->input('user_id'));
    $formField = $request->validate(
      [
        'division_id' => ['required', 'numeric', 'gt:0', function ($attribute, $value, $fail) {
          // check exists or not
          $divisionCheck = $this->division->where('id', $value)->exists();
          !$divisionCheck && $fail('Not exists');
        }],
        'company_name' => 'required|max:200',
        'email' => 'required|email|max:200',
        'mobile' => 'required|max:14',
        'address' => 'nullable|max:250',
        'name' => 'nullable|max:100',
        'reference' => 'nullable|max:100',
        'tin' => 'nullable|max:20',
        'circle' => 'nullable|max:50',
        'bin' => 'required',
        'vat_username' => 'required',
        'vat_password' => 'required',
        'user_id' => ['nullable', 'numeric', 'gt:0', function ($attribute, $value, $fail) use ($user) {
          if (in_array($user->role->name, ['superadmin', 'admin'])) {
            $fail('Not assignable to ' . $user->role->name);
          }
        }]
      ],
      [
        'division_id.required' => 'Division required',
        'division_id.numeric' => 'Number only',
        'division_id.gt' => 'Number > 0',
        'company_name.required' => 'Company name required',
        'company_name.max' => 'Max 200 char',
        'email.required' => 'Email required',
        'email.email' => 'Format not valid',
        'email.max' => 'Max 200 char',
        'mobile.required' => 'Mobile required',
        'mobile.max' => 'Max 14 char',
        'address.max' => 'Max 250 char',
        'name.max' => 'Max 100 char',
        'reference.max' => 'Max 100 char',
        'tin.max' => 'Max 20 char',
        'circle.max' => 'Max 50 char',
        'bin.required' => 'BIN required',
        'vat_username.required' => 'VAT username required',
        'vat_password.required' => 'VAT password required',
        'user_id.numeric' => 'Number only',
        'user_id.gt' => 'Number > 0',
      ]
    );

    $this->client->where('id', $id)->update($formField);

    return back()->with('message', 'Client updated');
  }

  // function call from client page for delete
  public function destroy($id)
  {
    $ids = explode(',', $id);
    $this->client->whereIn('id', $ids)->delete();
    return back()->with('message', 'Client deleted');
  }

  // function call for dropdown. it needs SelectInfinite component. It is send json response
  public function clientDropdown()
  {
    $user = Auth::user();
    $client = $this->client->with(['division', 'user', 'user.role'])
      ->when(request('search'), function ($query, $search) {
        $query->whereHas('division', function ($query) use ($search) {
          $query->where('name', 'like', '%' . strtolower($search) . '%');
        })->orWhere('bin', 'like', '%' . strtolower($search) . '%')
          ->orWhereHas('user', fn($query) => $query->where('email', 'like', '%' . strtolower($search) . '%'))
          ->orWhereHas('user.role', fn($query) => $query->where('name', 'like', '%' . strtolower($search) . '%'))
          ->orWhere('company_name', 'like', '%' . strtolower($search) . '%')
          ->orWhere('email', 'like', '%' . strtolower($search) . '%')
          ->orWhere('mobile', 'like', '%' . strtolower($search) . '%')
          ->orWhere('reference', 'like', '%' . strtolower($search) . '%');
      })->whereNotIn('status', ['0']);

    if (!in_array($user->role->name, ['admin'])) {
      $client->where('user_id', $user->id);
    }

    $result = $client->orderBy('id', 'desc')->paginate((int)request('size') ? (int)request('size') : 10);
    return response()->json(['client' => $result]);
  }

  // function call from client page status click
  public function statusUpdate(Request $request, $id)
  {
    $formField = $request->validate(
      ['status' => "required|in:0,1"],
      [
        'status.required' => 'Status required',
        'status.in' => 'Status will be 0 or 1',
      ]
    );

    $this->client->where('id', $id)->update($formField);
    return back()->with('message', 'Client status updated');
  }
}
