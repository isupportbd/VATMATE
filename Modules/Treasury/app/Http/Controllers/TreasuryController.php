<?php

namespace Modules\Treasury\Http\Controllers;

use App\Events\CommonEvent;
use App\Http\Controllers\Controller;
use App\Traits\ChecksUserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Branch\Models\Branch;
use Modules\Client\Models\Client;
use Modules\Treasury\Models\Treasury;

class TreasuryController extends Controller
{
  use ChecksUserRole;

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
    $search = trim((string) $request->input('search', ''));
    $termLike   = $search !== '' ? "%$search%" : null;

    $treasury = $this->treasury->with(['client.division', 'branch.bank', 'branch.division'])
      ->when(!$this->hasRole(['admin']), fn($q) => $q->whereHas('client', fn($q) => $q->where('user_id', $user->id)))
      ->when($search !== '', function ($query) use ($termLike) {
        $query->where(function ($query) use ($termLike) {
          $query->where('tax_period', 'ilike', $termLike)
            ->orWhereHas('client', function ($query) use ($termLike) {
              $query->where('bin', 'ilike', $termLike)
                ->orWhere('company_name', 'ilike', $termLike);
            })
            ->orWhereHas('branch', function ($query) use ($termLike) {
              $query->where('name', 'ilike', $termLike)
                ->orWhereHas('bank', fn($query) => $query->where('name', 'ilike', $termLike))
                ->orWhereHas('division', fn($query) => $query->where('name', 'ilike', $termLike));
            });
        });
      })->orderBy('id', 'desc')
      ->paginate((int)request('size', 10))
      ->withQueryString();

    return Inertia("treasury/index", ['treasury' => $treasury, 'search' => $search]);
  }

  // function call from treasuty page for create
  public function store(Request $request)
  {
    $user = Auth::user();

    $formField = $request->validate(
      [
        'client_id' => ['required', 'numeric', 'gt:0', function ($attribute, $value, $fail) use ($user) {
          $client = $this->client->find($value);

          if (!$client) return $fail('Client does not exist');

          if ($client->status == 0) return $fail('BIN inactive');

          if (!$this->hasRole(['admin']) && $client->user_id !== $user->id) return $fail('Unauthorised');
        }],
        'branch_id' => ['required', 'numeric', 'gt:0', function ($attribute, $value, $fail) {
          $branch = $this->branch->find($value);
          if (!$branch) return $fail('Not exists');
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

    // realtime data
    event(new CommonEvent($user->id));

    return back()->with('message', 'Treasury created');
  }

  // function call from treasury page as api for same page
  public function show($id)
  {
    $user = Auth::user();

    $treasury = $this->treasury->with(['client.division', 'client.user', 'branch.bank', 'branch.division'])->find($id);

    if (!$this->hasRole(['admin']) && ($treasury?->client->user_id !== $user->id)) {
      return response()->json(['error' => 'Unauthorised']);
    }

    return response()->json(['treasury' => $treasury]);
  }

  // function call form treasury page for update
  public function update(Request $request, $id)
  {
    $user = Auth::user();

    $formField = $request->validate(
      [
        'client_id' => ['required', 'numeric', 'gt:0', function ($attribute, $value, $fail) use ($user) {
          $client = $this->client->find($value);

          if (!$client) return $fail('Client does not exist');

          if ($client->status == 0) return $fail('BIN inactive');

          if (!$this->hasRole(['admin']) && $client->user_id !== $user->id) return $fail('Unauthorised');
        }],
        'branch_id' => ['required', 'numeric', 'gt:0', function ($attribute, $value, $fail) {
          $branch = $this->branch->find($value);
          if (!$branch) return $fail('Not exists');
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

    // realtime data
    event(new CommonEvent($user->id));

    return back()->with('message', 'Treasury updated');
  }

  // function call from treasury page for delete
  public function destroy($id)
  {
    $user = Auth::user();

    $ids = explode(',', $id);

    $treasury = $this->treasury->with(['client'])->whereIn('id', $ids)->get();

    if (!$treasury) {
      return back()->with('error', 'No data found');
    }

    if (!$this->hasRole(['admin'])) {
      $permission = $treasury->filter(function ($row) use ($user) {
        return $row->client?->user_id !== $user->id;
      });

      if ($permission->isNotEmpty()) {
        return back()->with('error', 'Unauthorised.');
      }
    }

    $this->treasury->whereIn('id', $ids)->delete();

    // realtime data
    event(new CommonEvent($user->id));

    return back()->with('message', 'Treasury deleted');
  }
}
