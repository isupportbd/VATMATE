<?php

namespace Modules\Addition\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Addition\Models\Addition;
use Modules\Client\Models\Client;
use Modules\Costsheet\Models\Costsheet;
use Modules\Form43\Models\Form43;
use Modules\Form621\Models\Form621;
use Throwable;

class AdditionController extends Controller
{
  public $addition;
  public $costsheet;
  public $form43;
  public $form621;
  public $client;
  public function __construct(Addition $addition, Costsheet $costsheet, Form43 $form43, Client $client, Form621 $form621)
  {
    $this->addition = $addition;
    $this->costsheet = $costsheet;
    $this->form43 = $form43;
    $this->client = $client;
    $this->form621 = $form621;
  }

  // function call automatically from addition page
  public function index(Request $request, $id)
  {
    $user = Auth::user();
    $isAdmin = in_array($user->role->name, ['admin']);
    $search = $request->input('search');

    $addition = $this->addition->select('id', 'form43_id', 'costsheet_id', 'rate', 'value')->with(['form43.client:id,bin,company_name', 'form43.item:id,name', 'costsheet:id,name'])
      ->when(!$isAdmin, fn($q) => $q->whereHas('form43.client', fn($query) => $query->where('user_id', $user->id)))
      ->when($search, function ($query) use ($search) {
        $query->whereHas('costsheet', fn($query) => $query->where('name', 'like', '%' . strtolower($search) . '%'));
      })
      ->where('form43_id', $id)
      ->orderBy('id', 'desc')
      ->paginate((int)request('size', 10))
      ->withQueryString();

    // form43 single from form43 class show method. if we want we can move it as a trait
    $form43 = $this->form43->with(['client:id,bin,user_id', 'item:id,name', 'additionsum'])
      ->when(!$isAdmin, fn($q) => $q->whereHas('client', fn($query) => $query->where('user_id', $user->id)))
      ->select('id', 'client_id', 'item_id', 'unit_price')
      ->find($id);

    return Inertia("addition/index", ['addition' => $addition, 'search' => $search, 'form43' => $form43]);
  }

  // function call from addition page for create
  public function store(Request $request)
  {
    $user = Auth::user();
    $isAdmin = in_array($user->role->name, ['admin']);

    $permission = $this->addition->with(['form43.client'])->whereHas('form43', fn($q) => $q->where('id', $request->input('form43_id')))->first();
    if (!$isAdmin && $permission?->form43?->client->user_id !== $user->id) {
      return back()->with('error', 'Unauthorised');
    }

    $inUse = $this->form621->where('form43_id', $request->form43_id)->first();
    if ($inUse) {
      return back()->with('error', 'Can not insert: already used in 6.2.1');
    }

    $formField = $request->validate(
      [
        'form43_id' => ['required', 'numeric', 'gt:0', function ($attribute, $value, $fail) use ($user, $isAdmin) {
          $form43 = $this->form43->where('id', $value)->exists();
          if (!$form43) return $fail('Not exists');
        }],
        'costsheet_id' => ['required', 'numeric', 'gt:0', function ($attribute, $value, $fail) use ($request) {
          $unique = $this->addition->where('form43_id', $request->input('form43_id'))->where('costsheet_id', $request->input('costsheet_id'))->exists();
          if ($unique) return $fail('Exists, change your input');
          // check exists or not
          $costsheetCheck = $this->costsheet->where('id', $value)->exists();
          if (!$costsheetCheck) return $fail('Not exists');
        }],
        'value' => 'required|numeric|gt:0', // value = (value / 4.3 unit_price) * 100
      ],
      [
        'form43_id.required' => 'Form 4.3 required',
        'form43_id.numeric' => 'Number only',
        'form43_id.gt' => 'Number > 0',
        'costsheet_id.required' => 'Costsheet required',
        'costsheet_id.numeric' => 'Number only',
        'costsheet_id.gt' => 'Number > 0',
        'value.required' => 'Value required',
        'value.numeric' => 'Number only',
        'value.gt' => 'Number > 0',
      ]
    );

    try {
      DB::transaction(function () use ($formField, $request) {
        $value = $request->input('value');
        $form43 = $this->form43->find($request->input('form43_id'));
        if (!empty($form43)) {
          $rate = ($value / $form43->unit_price) * 100;
          $formField['rate'] = number_format($rate, 2, ".", "");
        }

        $this->addition->create($formField);
      });

      return back()->with('message', 'Addition created');
    } catch (Throwable $e) {
      return back()->with("error", $e->getMessage());
    }
  }

  // function call from addition page as api for same page
  public function show($id)
  {
    $user = Auth::user();
    $isAdmin = in_array($user->role->name, ['admin']);

    $addition = $this->addition->with(['form43.client', 'form43.item', 'costsheet'])->find($id);

    if (!$isAdmin && $addition?->form43?->client->user_id !== $user->id) {
      return response()->json(['error' => 'Unauthorised']);
    }

    return response()->json(['addition' => $addition]);
  }

  // function call form addition page for update
  public function update(Request $request, $id)
  {
    $user = Auth::user();
    $isAdmin = in_array(optional($user->role)->name, ['admin']);

    $permission = $this->addition->with(['form43.client'])->where('id', $id)->first();
    if (!$isAdmin && $permission?->form43?->client->user_id !== $user->id) {
      return back()->with('error', 'Unauthorised');
    }

    $inUse = $this->form621->where('form43_id', $request->form43_id)->first();
    if ($inUse) {
      return back()->with('error', 'Can not update: already used in 6.2.1');
    }

    $formField = $request->validate(
      [
        'form43_id' => ['required', 'numeric', 'gt:0', function ($attribute, $value, $fail) {
          // check exists or not
          $form43 = $this->form43->where('id', $value)->exists();
          if (!$form43) return $fail('Not exists');
        }],
        'costsheet_id' => ['required', 'numeric', 'gt:0', function ($attribute, $value, $fail) use ($request) {
          // check exists or not
          $costsheetCheck = $this->costsheet->where('id', $value)->exists();
          if (!$costsheetCheck) return $fail('Not exists');
        }],
        'value' => 'required|numeric|gt:0',
      ],
      [
        'form43_id.required' => 'Form 4.3 required',
        'form43_id.numeric' => 'Number only',
        'form43_id.gt' => 'Number > 0',
        'costsheet_id.required' => 'Costsheet required',
        'costsheet_id.numeric' => 'Number only',
        'costsheet_id.gt' => 'Number > 0',
        'value.required' => 'Value required',
        'value.numeric' => 'Number only',
        'value.gt' => 'Number > 0',
      ]
    );

    try {
      DB::transaction(function () use ($formField, $request, $id) {
        $value = $request->input('value');
        $form43 = $this->form43->find($request->input('form43_id'));
        if (!empty($form43)) {
          $rate = ($value / $form43->unit_price) * 100;
          $formField['rate'] = number_format($rate, 2, ".", "");
        }

        $this->addition->where('id', $id)->update($formField);
      });

      return back()->with('message', 'Addition updated');
    } catch (Throwable $e) {
      return back()->with("error", $e->getMessage());
    }
  }

  // function call from addition page for delete
  public function destroy($id)
  {
    $user = Auth::user();
    $isAdmin = in_array(optional($user->role)->name, ['admin']);

    $ids = explode(',', $id);

    // Load additions with related form43 -> client
    $additions = $this->addition->with('form43.client')->whereIn('id', $ids)->get();
    if ($additions->isEmpty()) {
      return back()->with('error', 'No data found.');
    }

    // If not admin, ensure all additions belong to the current user's clients
    if (! $isAdmin) {
      $unauthorized = $additions->filter(function ($row) use ($user) {
        return optional($row->form43?->client)->user_id !== $user->id;
      });

      if ($unauthorized->isNotEmpty()) {
        return back()->with('error', 'Unauthorised.');
      }
    }

    // Check whether any of the related form43 rows are used in form621 (6.2.1)
    $form43Ids = $additions->pluck('form43_id')->filter()->unique()->values()->all();
    if (!empty($form43Ids)) {
      $inUse = $this->form621->whereIn('form43_id', $form43Ids)->exists();
      if ($inUse) {
        return back()->with('error', 'Cannot delete: already used in 6.2.1');
      }
    }

    // Safe to delete
    try {
      DB::transaction(function () use ($ids) {
        $this->addition->whereIn('id', $ids)->delete();
      });
    } catch (Throwable $e) {
      Log::error('Failed to delete additions: ' . $e->getMessage());
      return back()->with('error', 'Failed to delete additions. Please try again.');
    }

    return back()->with('message', 'Addition(s) deleted successfully.');
  }
}
