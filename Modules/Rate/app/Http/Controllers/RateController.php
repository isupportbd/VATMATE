<?php

namespace Modules\Rate\Http\Controllers;

use App\Events\CommonEvent;
use App\Http\Controllers\Controller;
use App\Traits\ChecksUserRole;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Modules\Addition\Models\Addition;
use Modules\Client\Models\Client;
use Modules\Form43\Models\Form43;
use Modules\Item\Models\Item;
use Modules\Rate\Models\Rate;
use Modules\Vatfactor\Models\Vatfactor;
use Throwable;

class RateController extends Controller
{
  use ChecksUserRole;

  public $client;
  public $item;
  public $rate;
  public $form43;
  public $vatfactor;
  public $addition;
  public function __construct(Client $client, Item $item, Rate $rate, Form43 $form43, Vatfactor $vatfactor, Addition $addition)
  {
    $this->client = $client;
    $this->item = $item;
    $this->rate = $rate;
    $this->form43 = $form43;
    $this->vatfactor = $vatfactor;
    $this->addition = $addition;
  }

  /**
   * Display a listing of the resource.
   */
  public function index(Request $request)
  {
    $user   = Auth::user();
    $search = trim((string) $request->input('search', ''));
    $termLike   = $search !== '' ? $search . '%' : null;

    $query = $this->rate->with(['client', 'client.user', 'client.user.role', 'item'])
      ->when(!$this->hasRole(['admin']), fn($q) => $q->whereHas('client', fn($query) => $query->where('user_id', $user->id))->where('last', 1))
      ->when($search !== '', function ($query) use ($termLike) {
        $query->where(function ($query) use ($termLike) {
          $query->where('lc_or_invoice_no', 'like', $termLike)
            ->orWhereHas('client', function ($query) use ($termLike) {
              $query->where('company_name', 'like', $termLike)
                ->orWhere('mobile', 'like', $termLike)
                ->orWhere('bin', 'like', $termLike);
            })->orWhereHas('item', function ($query) use ($termLike) {
              $query->where('name', 'like', $termLike)
                ->orWhere('hs_code', 'like', $termLike);
            });
        });
      })
      ->orderBy('id', 'desc')
      ->paginate((int)request('size', 10))
      ->withQueryString();

    return Inertia("rate/index", ['rate' => $query, 'search' => $search]);
  }

  /**
   * Store a newly created resource in storage.
   */
  public function store(Request $request)
  {
    $user = Auth::user();

    $clientId = $request->input('client_id');
    $itemId = $request->input('item_id');

    $form43 = $this->form43->with(['additionsum'])->where('client_id', $clientId)->where('item_id', $itemId)->where('last', 1)->first();

    if (!$form43) {
      return back()->withErrors(['item_id' => '4.3 not found'])->withInput();
    }

    $formField = $request->validate(
      [
        'client_id' => ['required', 'numeric', 'gt:0', function ($attribute, $value, $fail) use ($user) {
          $client = $this->client->find($value);

          if (!$client) return $fail('Client does not exist');

          if ($client->status == 0) return $fail('BIN inactive');

          if (!$this->hasRole(['admin']) && $client->user_id !== $user->id) return $fail('Unauthorised');
        }],
        'item_id' => ['required', 'numeric', 'gt:0', function ($attribute, $value, $fail) use ($form43) {
          // Check if item exists
          $item = $this->item->find($value);
          if (!$item) return $fail('Item not exists');

          if (!$form43) return $fail('4.3 not found');

          if (!$form43->additions()->exists()) return $fail('4.3 has no addition');

          // if (!$form43) {
          //   throw ValidationException::withMessages([
          //     $attribute => ['4.3 not found']
          //   ]);
          // }
        }],
        'date' => ['required', 'date_format:"Y-m-d"', function ($attribute, $value, $fail) {
          $date = Carbon::parse($value, 'Asia/Dhaka')->startOfDay();
          $today = Carbon::today('Asia/Dhaka')->startOfDay();

          // Stop if entry date is over current date (future date)
          if ($date->gt($today)) {
            return $fail("Greater than today");
          }
        }],
        'value' => 'required|numeric|gt:0',
        'unit_price' => ['required', 'numeric', 'gt:0', function ($attribute, $value, $fail) use ($form43) {
          $additionSum = optional($form43->additionsum())->first();
          $unitPrice = optional($form43)->unit_price;
          $form43ForFactor = ($unitPrice + $additionSum->valuesum);

          $calc = !empty($form43ForFactor) ? ((($value - $form43ForFactor) / $form43ForFactor) * 100) : null;

          $vatfactor = $this->vatfactor->where('name', 'vatfact')->value('value');

          if (!empty($form43ForFactor) && $vatfactor !== null) {
            if ($calc >  (float)$vatfactor) return $fail('Over ' . $vatfactor . ' (' . number_format($calc, 2, '.', '') . '%)');
            if ($calc < -(float)$vatfactor) return $fail('Less ' . $vatfactor . ' (' . number_format($calc, 2, '.', '') . '%)');
          }
        }],
        'sd' => 'required|numeric|gte:0|decimal:0,2|between:0,100.00',
        'vat' => 'required|numeric|gte:0|decimal:0,2|between:0,100.00',
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
        'value.required' => 'Value required',
        'value.numeric' => 'Number only',
        'value.gt' => 'Number > 0',
        'unit_price.required' => 'Unit price required',
        'unit_price.numeric' => 'Number only',
        'unit_price.gt' => 'Number > 0',
        'sd.required' =>  'SD required',
        'sd.numeric' =>  'Number only',
        'sd.gte' =>  '(+)Number only',
        'sd.between' =>  '0 to 100.00',
        'vat.required' =>  'VAT required',
        'vat.numeric' =>  'Number only',
        'vat.gte' =>  '(+)Number only',
        'vat.between' =>  '0 to 100.00',
      ]
    );

    try {
      DB::transaction(function () use ($formField, $clientId, $itemId, $request) {
        $val = (float) $request->input('value');
        $vat = (float) $request->input('vat');
        $sd  = (float) $request->input('sd');

        // last form43 id find out with client_id, item_id
        $last = $this->rate->where(['client_id' => $clientId, 'item_id' => $itemId])->groupBy(['client_id', 'item_id'])->max('id');
        // last was 1. in this case update last will be update 0 then created last will be 1
        !empty($this->rate) && $this->rate->where('id', $last)->update(['last' => 0]);
        // last will be one
        $formField['last'] = 1;

        $uprice = ($val - ($val * ($vat / (100 + $vat)))) - (($val - ($val * ($vat / (100 + $vat)))) * ($sd / (100 + $sd)));
        $formField['unit_price'] = number_format($uprice, 2, '.', '');

        unset($formField['value']);
        $this->rate->create($formField);
      });

      // realtime data
      event(new CommonEvent($user->id));

      return back()->with('message', 'Rate created');
    } catch (Throwable $e) {
      return back()->with("error", $e->getMessage());
    }
  }

  /**
   * Show the specified resource.
   */
  public function show($id)
  {
    $user = Auth::user();

    $query = $this->rate->with(['client', 'item'])->find($id);

    if (!$this->hasRole(['admin']) && (optional($query->client)->user_id !== $user->id)) {
      return response()->json(['error' => 'Unauthorised']);
    }

    $additionsSub = $this->addition->select('form43_id', DB::raw('SUM(value) as valuesum'))->groupBy('form43_id');

    $form43Last = $this->form43
      ->with(['client', 'item', 'purchaseunit:id,name', 'saleunit:id,name']) // additionsum rel not required here
      ->leftJoinSub($additionsSub, 'additionsum', function ($join) {
        $join->on('form43s.id', '=', 'additionsum.form43_id');
      })
      ->where('client_id', $query->client_id)
      ->where('item_id', $query->item_id)
      ->where('last', 1)
      ->select('form43s.*', DB::raw('ROUND(form43s.unit_price + COALESCE(additionsum.valuesum,0),2) as unit_price_with_addition'))
      ->first();

    return response()->json(['rate' => $query, 'form43last' => $form43Last]);
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(Request $request, $id)
  {
    $user = Auth::user();

    $clientId = $request->input('client_id');
    $itemId = $request->input('item_id');

    $form43 = $this->form43->with(['additionsum'])->where('client_id', $clientId)->where('item_id', $itemId)->where('last', 1)->first();

    if (!$form43) {
      return back()->withErrors(['item_id' => '4.3 not found'])->withInput();
    }

    $formField = $request->validate(
      [
        'client_id' => ['required', 'numeric', 'gt:0', function ($attribute, $value, $fail) use ($user) {
          $client = $this->client->find($value);

          if (!$client) return $fail('Client does not exist');

          if ($client->status == 0) return $fail('BIN inactive');

          if (!$this->hasRole(['admin']) && $client->user_id !== $user->id) return $fail('Unauthorised');
        }],
        'item_id' => ['required', 'numeric', 'gt:0', function ($attribute, $value, $fail) {
          $item = $this->item->find($value);
          if (!$item) return $fail('Not exists');
        }],
        'date' => ['required', 'date_format:"Y-m-d"', function ($attribute, $value, $fail) {
          $date = Carbon::parse($value, 'Asia/Dhaka')->startOfDay();
          $today = Carbon::today('Asia/Dhaka')->startOfDay();

          // Stop if entry date is over current date (future date)
          if ($date->gt($today)) {
            return $fail("Greater than today");
          }
        }],
        'value' => 'required|numeric|gt:0',
        'unit_price' => ['required', 'numeric', 'gt:0', function ($attribute, $value, $fail) use ($form43) {
          $additionSum = optional($form43->additionsum())->first();
          $unitPrice = optional($form43)->unit_price;
          $form43ForFactor = ($unitPrice + $additionSum->valuesum);

          $calc = !empty($form43ForFactor) ? ((($value - $form43ForFactor) / $form43ForFactor) * 100) : null;

          $vatfactor = $this->vatfactor->where('name', 'vatfact')->value('value');

          if (!empty($form43ForFactor) && $vatfactor !== null) {
            if ($calc >  (float)$vatfactor) return $fail('Over ' . $vatfactor . ' (' . number_format($calc, 2, '.', '') . '%)');
            if ($calc < -(float)$vatfactor) return $fail('Less ' . $vatfactor . ' (' . number_format($calc, 2, '.', '') . '%)');
          }
        }],
        'sd' => 'required|numeric|gte:0|decimal:0,2|between:0,100.00',
        'vat' => 'required|numeric|gte:0|decimal:0,2|between:0,100.00',
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
        'value.required' => 'Value required',
        'value.numeric' => 'Number only',
        'value.gt' => 'Number > 0',
        'unit_price.required' => 'Unit price required',
        'unit_price.numeric' => 'Number only',
        'unit_price.gt' => 'Number > 0',
        'sd.required' =>  'SD required',
        'sd.numeric' =>  'Number only',
        'sd.gte' =>  '(+)Number only',
        'sd.between' =>  '0 to 100.00',
        'vat.required' =>  'VAT required',
        'vat.numeric' =>  'Number only',
        'vat.gte' =>  '(+)Number only',
        'vat.between' =>  '0 to 100.00',
      ]
    );

    try {
      DB::transaction(function () use ($formField, $request, $id) {
        $val = (float) $request->input('value');
        $vat = (float) $request->input('vat');
        $sd  = (float) $request->input('sd');

        $uprice = ($val - ($val * ($vat / (100 + $vat)))) - (($val - ($val * ($vat / (100 + $vat)))) * ($sd / (100 + $sd)));
        $formField['unit_price'] = number_format($uprice, 2, '.', '');

        unset($formField['value']);
        $this->rate->where('id', $id)->update($formField);
      });
    } catch (Throwable $e) {
      return back()->with("error", $e->getMessage());
    }

    // realtime data
    event(new CommonEvent($user->id));

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
        $rate = $this->rate->with(['client', 'item'])->find($id);

        if (!$rate) {
          return back()->with('error', 'No data found');
        }

        if (!$this->hasRole(['admin']) && ($rate->client?->user_id !== $user->id)) {
          return back()->with('error', 'Unauthorised');
        }

        if ($rate->last != 1) {
          return back()->with('error', 'Can not delete: not last combination');
        }

        // find out current id rate
        $rateById = $rate;
        // delete current id
        $rate->delete();

        // find out last rate row with rateById
        $newLastId = $this->rate->where('client_id', $rateById->client_id)->where('item_id', $rateById->item_id)->max('id');
        if ($newLastId) {
          $this->rate->where('id', $newLastId)->update(['last' => 1]);
        }
      });

      // realtime data
      event(new CommonEvent($user->id));

      return back()->with('message', 'Rate deleted');
    } catch (Throwable $e) {
      return back()->with("error", $e->getMessage());
    }
  }

  /**
   * Find last from Rate
   * combination will be: client, item, last
   */
  public function rateLast(Request $request)
  {
    $user = Auth::user();

    $clientId = $request->input('client_id');
    $itemId   = $request->input('item_id');

    // Step 1: Get the last rate
    $rateLast = $this->rate->with(['client', 'item'])
      ->when(!$this->hasRole(['admin']), fn($q) => $q->whereHas('client', fn($q) => $q->where('user_id', $user->id)))
      ->where('client_id', $clientId)
      ->where('item_id', $itemId)
      ->where('last', 1)
      ->first();

    // Step 2: Get last form43 with additions
    $form43 = $this->form43->with(['client', 'item', 'purchaseunit:id,name', 'saleunit:id,name', 'additions'])
      ->when(!$this->hasRole(['admin']), fn($q) => $q->whereHas('client', fn($q) => $q->where('user_id', $user->id)))
      ->where('client_id', $clientId)
      ->where('item_id', $itemId)
      ->where('last', 1)
      ->first();

    if (!$form43) {
      return response()->json(['ratelast' => $rateLast, 'ratemsg' => null, 'message' => '4.3 not found']);
    }

    if (!$form43->additions()->exists()) {
      return response()->json(['ratelast' => $rateLast, 'ratemsg' => null, 'message' => '4.3 has no addition']);
    }

    if (($form43 && $form43->additions()->exists()) && !$rateLast) {
      return response()->json(['ratelast' => $rateLast, 'ratemsg' => 'Rate required', 'message' => null]);
    }

    return response()->json(['ratelast' => $rateLast, 'ratemsg' => null, 'message' => null]);
  }
}
