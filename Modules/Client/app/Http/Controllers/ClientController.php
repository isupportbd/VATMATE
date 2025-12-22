<?php

namespace Modules\Client\Http\Controllers;

use App\Events\CommonEvent;
use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Traits\ChecksUserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Client\Models\Client;
use Modules\Division\Models\Division;

class ClientController extends Controller
{
  use ChecksUserRole;

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
    $search = trim((string) $request->input('search', ''));
    $termLike   = $search !== '' ? $search . '%' : null;

    $query = $this->client->with(['division', 'user.role'])
      ->when(!$this->hasRole(['admin']), fn($q) =>  $q->where('user_id', $user->id))
      ->when($search !== '', function ($query) use ($termLike) {
        $query->where(function ($query) use ($termLike) {
          $query->where('company_name', 'like', $termLike)
            ->orWhere('bin', 'like', $termLike)
            ->orWhere('email', 'like', $termLike)
            ->orWhere('mobile', 'like', $termLike)
            ->orWhere('reference', 'like', $termLike)
            ->orWhereHas('division', fn($query) => $query->where('name', 'like', $termLike))
            ->orWhereHas('user', fn($query) => $query->where('email', 'like', $termLike));
        })->whereNotIn('user_id', NULL);
      })->orderBy('id', 'desc')->paginate((int)request('size', 10))->withQueryString();

    return Inertia('client/index', ['client' => $query, 'search' => $search]);
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
          if (!$divisionCheck) return $fail('Not exists');
        }],
        'company_name' => 'required|max:200',
        'email' => 'required|email|unique:clients,email|max:200',
        'mobile' => 'required|max:14',
        'address' => 'nullable|max:150',
        'name' => 'nullable|max:100',
        'reference' => 'nullable|max:100',
        'tin' => 'nullable|max:20',
        'circle' => 'nullable|max:50',
        'bin' => 'required|unique:clients,bin',
        'vat_username' => 'required',
        'vat_password' => 'required',
        'user_id' => ['nullable', 'numeric', 'gt:0', function ($attribute, $value, $fail) use ($user) {
          if (in_array($user->role->name, ['superadmin', 'admin'])) {
            return $fail('Not assignable to ' . $user->role?->name);
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
    // realtime data
    event(new CommonEvent(Auth::id()));

    return back()->with('message', 'Client created');
  }

  // function call from client page for single show
  public function show($id)
  {
    $user = Auth::user();

    $client = $this->client->with(['division', 'user', 'user.role'])->find($id);

    if (!$this->hasRole(['admin']) && (optional($client)->user_id !== $user->id)) {
      return response()->json(['error' => 'Unauthorised']);
    }

    return response()->json(['client' => $client]);
  }

  // function call from client page for update
  public function update(Request $request, $id)
  {
    $user = Auth::user();

    $client = $this->client->find($id);
    if (!$this->hasRole(['admin']) && (optional($client)->user_id !== $user->id)) {
      return back()->with('error', 'Unauthorised');
    }

    $formField = $request->validate(
      [
        'division_id' => ['required', 'numeric', 'gt:0', function ($attribute, $value, $fail) {
          // check exists or not
          $divisionCheck = $this->division->where('id', $value)->exists();
          if (!$divisionCheck) return $fail('Not exists');
        }],
        'company_name' => 'required|max:200',
        'email' => 'required|email|max:200',
        'mobile' => 'required|max:14',
        'address' => 'nullable|max:150',
        'name' => 'nullable|max:100',
        'reference' => 'nullable|max:100',
        'tin' => 'nullable|max:20',
        'circle' => 'nullable|max:50',
        'bin' => 'required',
        'vat_username' => 'required',
        'vat_password' => 'required',
        'user_id' => ['nullable', 'numeric', 'gt:0', function ($attribute, $value, $fail) use ($request) {
          $role = $this->user->find($request->input('user_id'));
          if (in_array($role->role->name, ['superadmin', 'admin'])) {
            return $fail('Not assignable to ' . $role->role->name);
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

    // realtime data
    event(new CommonEvent($user->id));

    return back()->with('message', 'Client updated');
  }

  // function call from client page for delete
  public function destroy($id)
  {
    $user = Auth::user();
    $ids = explode(',', $id);

    $client = $this->client->with(['user'])->whereIn('id', $ids)->get();

    if ($client->isEmpty()) {
      return back()->with('error', 'No data found');
    }

    if (!$this->hasRole(['admin'])) {
      $permission = $client->filter(function ($row) use ($user) {
        return $row->user_id !== $user->id;
      });

      if ($permission->isNotEmpty()) {
        return back()->with('error', 'Unauthorised.');
      }
    }

    $this->client->whereIn('id', $ids)->delete();

    // realtime data
    event(new CommonEvent($user->id));

    return back()->with('message', "Client deleted");
  }

  // function call for dropdown. it needs SelectInfinite component. It is send json response
  public function clientDropdown(Request $request)
  {
    $user = Auth::user();
    $search = trim((string) $request->input('search', ''));
    $termLike   = $search !== '' ? $search . '%' : null;

    $query = $this->client->with(['division', 'user.role'])
      ->when(!$this->hasRole(['admin']), fn($q) =>  $q->where('user_id', $user->id))
      ->when($search !== '', fn($q) => $q->where('bin', 'like', $termLike))
      ->whereNotIn('status', ['0'])
      ->orderBy('id', 'desc')
      ->paginate((int)request('size', 10));

    return response()->json(['client' => $query]);
  }

  // function call from client page status click
  public function statusUpdate(Request $request, $id)
  {
    $user = Auth::user();

    $formField = $request->validate(
      ['status' => "required|in:0,1"],
      ['status.required' => 'Status required', 'status.in' => 'Status will be 0 or 1']
    );

    $client = $this->client->where('id', $id)->first();

    if (!$this->hasRole(['admin']) && $client->user_id !== $user->id) {
      return back()->with('error', 'Unauthorised');
    }

    $client->update($formField);

    // realtime data
    event(new CommonEvent($user->id));

    return back()->with('message', 'Client status updated');
  }

  // function call from client page for release
  public function clientRelease($id)
  {
    $user = Auth::user();

    $client = $this->client->with(['user'])->find($id);

    if (!$client) {
      return back()->with('error', 'No data found');
    }

    if (!$this->hasRole(['admin']) && ($client->user_id !== $user->id)) {
      return back()->with('error', 'Unauthorised.');
    }

    $this->client->where('id', $id)->update(['user_id' => null, 'status' => ['0']]);

    // realtime data
    event(new CommonEvent($user->id));

    return back()->with('message', "Client released");
  }

  // function call for dropdown. it needs SelectInfinite component. It is send json response
  public function releaseClientDropdown(Request $request)
  {
    $search = trim((string) $request->input('search', ''));
    $termLike   = $search !== '' ? $search . '%' : null;

    $query = $this->client->with(['division', 'user.role'])
      ->when($search !== '', fn($q) => $q->where('bin', 'like', $termLike))
      ->where('user_id', NULL)
      ->orderBy('id', 'desc')
      ->paginate((int)request('size', 10));

    return response()->json(['client' => $query]);
  }
}
