<?php

namespace Modules\Rate\Http\Controllers;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Modules\Client\Models\Client;
use Modules\Item\Models\Item;
use Modules\Rate\Models\Rate;

class RateController extends Controller
{
  public $client;
  public $item;
  public $rate;
  public function __construct(Client $client, Item $item, Rate $rate)
  {
    $this->client = $client;
    $this->item = $item;
    $this->rate = $rate;
  }

  /**
   * Display a listing of the resource.
   */
  public function index(Request $request)
  {
    $user   = Auth::user();
    $isAdmin = in_array($user->role->name, ['admin']);
    $search = trim((string) $request->input('search', ''));

    $rate = $this->rate->with(['client', 'client.user', 'client.user.role', 'item'])
      ->when($search, function ($query) use ($search, $user, $isAdmin) {
        $query->where(function ($query) use ($search, $user, $isAdmin) {
          $query->orWhere('lc_or_invoice_no', 'like', "%{$search}%")
            ->orWhereHas('client', function ($query) use ($search, $user, $isAdmin) {
              $query->when(!$isAdmin, fn($q) => $q->where('user_id', $user->id));
              $query->where('company_name', 'like', "%{$search}%")
                ->orWhere('mobile', 'like', "%{$search}%")
                ->orWhere('bin', 'like', "%{$search}%");
            })->orWhereHas('item', function ($query) use ($search) {
              $query->where('name', 'like', "%{$search}%")
                ->orWhere('hs_code', 'like', "%{$search}%");
            });
        });
      });

    if (!$isAdmin) {
      $rate->whereHas('client', fn($query) => $query->where('user_id', $user->id));
    }

    $result = $rate->orderBy('id', 'desc')->paginate((int)request('size', 10))->withQueryString();

    return Inertia("rate/index", [
      'rate' => $result,
      'search' => $search
    ]);
  }

  /**
   * Store a newly created resource in storage.
   */
  public function store(Request $request)
  {
    $user = Auth::user();

    $formField = $request->validate(
      [
        'client_id' => ['required', 'numeric', 'gt:0', function ($attribute, $value, $fail) use ($user) {
          if (!in_array($user->role->name, ['admin'])) {
            $userCheck = $this->client->where('id', $value)->where('user_id', $user->id)->exists();
            // error show if role is not admin and client is not under him
            !$userCheck && $fail('Not your client');
          }

          // bin status check
          $clientbinStatusCheck = $this->client->where([['id', $value], ['status', '0']])->first();
          $clientbinStatusCheck && $fail("BIN inactive");

          // check exists or not
          $binCheck = $this->client->where('id', $value)->exists();
          !$binCheck && $fail('Not exists');
        }],
        'item_id' => ['required', 'numeric', 'gt:0', function ($attribute, $value, $fail) {
          // check exists or not
          $itemCheck = $this->item->where('id', $value)->exists();
          !$itemCheck && $fail('Not exists');
        }],
        'date' => 'required|date_format:"Y-m-d"',
        'unit_price' => 'required|numeric|gt:0',
      ],
      [
        'client_id.required' => 'BIN required',
        'client_id.numeric' => 'Number only',
        'client_id.gt' => 'Number > 0',
        'item_id.required' => 'Item required',
        'item_id.numeric' => 'Number only',
        'item_id.gt' => 'Number > 0',
        'date.required' => 'Date required',
        'date.date_format' => 'Invalid date',
        'unit_price.required' => 'Unit price required',
        'unit_price.numeric' => 'Number only',
        'unit_price.gt' => 'Number > 0',
      ]
    );

    try {
      DB::transaction(function () use ($formField, $request) {
        // last form43 id find out with client_id, item_id
        $last = $this->rate->where(['client_id' => $request->client_id, 'item_id' => $request->item_id])->groupBy(['client_id', 'item_id'])->max('id');
        // last was 1. in this case update last will be update 0 then created last will be 1
        !empty($this->rate) && $this->rate->where('id', $last)->update(['last' => 0]);
        // last will be one
        $formField['last'] = 1;

        $this->rate->create($formField);
      });

      return back()->with('message', 'Rate created');
    } catch (Exception $e) {
      return back()->with("error", $e->getMessage());
    }
  }

  /**
   * Show the specified resource.
   */
  public function show($id)
  {
    $user = Auth::user();
    $rate = $this->rate->with(['client', 'item']);

    if (!in_array($user->role->name, ['admin'])) {
      $rate->whereHas('client', fn($query) => $query->where('user_id', $user->id));
    }

    $result = $rate->find($id);
    $msg = $result ? ['rate' => $result] : ['msg' => 'Nothing found'];
    return response()->json($msg);
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(Request $request, $id)
  {
    $user = Auth::user();

    $formField = $request->validate(
      [
        'client_id' => ['required', 'numeric', 'gt:0', function ($attribute, $value, $fail) use ($user) {
          if (!in_array($user->role->name, ['admin'])) {
            $userCheck = $this->client->where('id', $value)->where('user_id', $user->id)->exists();
            // error show if role is not admin and client is not under him
            !$userCheck && $fail('Not your client');
          }

          // bin status check
          $clientbinStatusCheck = $this->client->where([['id', $value], ['status', '0']])->first();
          $clientbinStatusCheck && $fail("BIN inactive");

          // check exists or not
          $binCheck = $this->client->where('id', $value)->exists();
          !$binCheck && $fail('Not exists');
        }],
        'item_id' => ['required', 'numeric', 'gt:0', function ($attribute, $value, $fail) {
          // check exists or not
          $itemCheck = $this->item->where('id', $value)->exists();
          !$itemCheck && $fail('Not exists');
        }],
        'date' => 'required|date_format:"Y-m-d"',
        'unit_price' => 'required|numeric|gt:0',
      ],
      [
        'client_id.required' => 'BIN required',
        'client_id.numeric' => 'Number only',
        'client_id.gt' => 'Number > 0',
        'item_id.required' => 'Item required',
        'item_id.numeric' => 'Number only',
        'item_id.gt' => 'Number > 0',
        'date.required' => 'Date required',
        'date.date_format' => 'Invalid date',
        'unit_price.required' => 'Unit price required',
        'unit_price.numeric' => 'Number only',
        'unit_price.gt' => 'Number > 0',
      ]
    );

    // try {
    //   DB::transaction(function () use ($formField, $request, $id) {
    //     // last form43 id find out with client_id, item_id
    //     $last = $this->rate->where(['client_id' => $request->client_id, 'item_id' => $request->item_id])->groupBy(['client_id', 'item_id'])->max('id');
    //     // last was 1. in this case update last will be update 0 then created last will be 1
    //     !empty($this->rate) && $this->rate->where('id', $last)->update(['last' => 0]);
    //     // last will be one
    //     $formField['last'] = 1;
    //   });
    // } catch (Exception $e) {
    //   return back()->with("error", $e->getMessage());
    // }

    $this->rate->where('id', $id)->update($formField);
    return back()->with('message', 'Rate updated');
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy($id)
  {
    $user = Auth::user();

    try {
      DB::transaction(function () use ($id, $user) {
        $rate = $this->rate->with(['client', 'item'])->where('id', $id);

        if (!in_array($user->role->name, ['admin'])) {
          $rate->whereHas('client', fn($query) => $query->where('user_id', $user->id));
        }

        // Fetch records before delete to check ownership
        $toDelete = $rate->get();
        // If no record belongs to user → block deletion
        if ($toDelete->isEmpty()) {
          return back()->with('error', 'Not your client data');
        }

        // find out current id rate
        $rateById = $this->rate->where('id', $id)->first();
        // delete current id
        $result = $rate->delete();

        // find out last rate with rateById
        $last = $this->rate->where([['client_id', $rateById->client_id], ['item_id', $rateById->item_id]])->groupBy('client_id', 'item_id')->max('id');
        $rateLast = $this->rate->where('id', $last)->first();
        // in this case we will delete this id than update previous last 1
        !empty($rateLast) && $rateLast->where('id', $last)->update(['last' => 1]);
      });

      return back()->with('message', 'Rate deleted');
    } catch (Exception $e) {
      return back()->with("error", $e->getMessage());
    }
  }
}
