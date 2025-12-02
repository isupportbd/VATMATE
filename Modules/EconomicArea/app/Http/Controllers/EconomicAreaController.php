<?php

namespace Modules\EconomicArea\Http\Controllers;

use App\Events\CommonEvent;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Client\Models\Client;
use Modules\EconomicArea\Events\EconomicAreaEvent;
use Modules\EconomicArea\Models\EconomicArea;
use Modules\Form621\Models\Form621;
use Modules\Item\Models\Item;
use Throwable;

class EconomicAreaController extends Controller
{
  public $economicarea;
  public $client;
  public $item;
  public $form621;
  public function __construct(EconomicArea $economicarea, Client $client, Item $item, Form621 $form621)
  {
    $this->economicarea = $economicarea;
    $this->client = $client;
    $this->item = $item;
    $this->form621 = $form621;
  }

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
        'item_id' => ['required', 'numeric', 'gt:0', function ($attribute, $value, $fail) {
          $item = $this->item->find($value);
          if (!$item) return $fail('Not exists');
        }]
      ],
      [
        'client_id.required' => 'Entity required',
        'client_id.numeric' => 'Number only',
        'client_id.gt' => 'Number > 0',
        'item_id.required' => 'Item required',
        'item_id.numeric' => 'Number only',
        'item_id.gt' => 'Number > 0'
      ]
    );

    try {
      // Avoid duplicate insert because of composite unique key
      EconomicArea::firstOrCreate($formField);

      // realtime data
      event(new CommonEvent($user->id));

      return back()->with('message', 'Economic area created');
    } catch (Throwable $e) {
      return back()->with('error', 'Insert failed: ' . $e->getMessage());
    }
  }

  public function index(Request $request)
  {
    $user = Auth::user();
    $isAdmin = in_array(optional($user->role)->name, ['admin']);
    $search = (string) $request->input('search');

    $query = $this->client
      ->when(!$isAdmin, fn($q) => $q->where('user_id', $user->id))
      ->when($search, function ($query) use ($search) {
        $query->where(function ($query) use ($search) {
          $query->where('company_name', 'like', "%{$search}%")
            ->orWhere('mobile', 'like', "%{$search}%")
            ->orWhere('bin', 'like', "%{$search}%")
            ->orWhereHas('items', function ($q) use ($search) {
              $q->where('name', 'like', "%{$search}%")
                ->orWhere('hs_code', 'like', "%{$search}%");
            });
        });
      });

    // 👇 Eager-load items and add a subselect that checks form621 for the pivot pair
    $query->with(['items' => function ($q) {
      $q->select('items.*')
        ->addSelect(DB::raw(
          "EXISTS(
                  SELECT 1
                  FROM form621s f
                  WHERE f.client_id = economic_areas.client_id
                    AND f.item_id   = economic_areas.item_id
          ) as has_form621"
        ));
    }]);

    $result = $query->orderBy('id', 'desc')->paginate((int) request('size', 10))->withQueryString();

    return Inertia('economicarea/index', ['economicarea' => $result, 'search' => $search]);
  }

  public function destroy($client_id, $item_id)
  {
    $user    = Auth::user();
    $isAdmin = in_array(optional($user->role)->name, ['admin']);

    // 1) Authorize: client must belong to current user unless admin
    $client = $this->client->when(!$isAdmin, fn($q) => $q->where('user_id', $user->id))->find($client_id);

    if (!$client) {
      return back()->with('error', 'Unauthorised');
    }

    // 2) Ensure the pivot row actually exists
    $pivotExists = $this->economicarea->where('client_id', $client_id)->where('item_id', $item_id)->exists();

    if (!$pivotExists) {
      return back()->with('error', 'Not exists');
    }

    // 3) Block if used in form621
    $inUse = $this->form621->where('client_id', $client_id)->where('item_id', $item_id)->exists();

    if ($inUse) {
      return back()->with('error', 'Cannot delete: used in 6.2.1');
    }

    // 4) Delete (detach) inside a transaction
    DB::transaction(function () use ($client_id, $item_id) {
      // either delete from pivot table...
      EconomicArea::where('client_id', $client_id)->where('item_id', $item_id)->delete();
    });

    // realtime data
    event(new CommonEvent($user->id));

    return back()->with('message', 'Economic area link deleted.');
  }

  // economic item for client (special logic)
  public function clientItemDropdown(Request $request)
  {
    $user = Auth::user();
    $isAdmin = in_array(optional($user->role)->name, ['admin']);
    $search = $request->query('search');
    $clientId = $request->input('client_id');

    if (!$clientId) {
      $empty = $this->item->whereRaw('1 = 0')->paginate((int)request('size', 10));
      return response(['item' => $empty, 'search' => $search]);
    }
    // parent class
    $client = $this->client->when(!$isAdmin, fn($q) => $q->where('user_id', $user->id))->findOrFail($clientId);
    // parent to sub class many to many relation
    $query = $client->items()->when($search, fn($query) => $query->where('name', 'like', '%' . strtolower($search) . '%'));

    $result = $query->orderBy('id', 'desc')->paginate((int)request('size', 10));

    return response()->json(['item' => $result, 'search' => $search]);
  }
}
