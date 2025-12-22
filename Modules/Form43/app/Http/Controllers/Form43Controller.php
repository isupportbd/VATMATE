<?php

namespace Modules\Form43\Http\Controllers;

use App\Events\CommonEvent;
use App\Http\Controllers\Controller;
use App\Traits\ChecksUserRole;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Client\Models\Client;
use Modules\Form43\Models\Form43;
use Modules\Form621\Models\Form621;
use Modules\Item\Models\Item;
use Modules\Unit\Models\Unit;
use Throwable;

class Form43Controller extends Controller
{
  use ChecksUserRole;

  public $client;
  public $criteria;
  public $form43;
  public $form621;
  public $item;
  public $unit;
  public function __construct(Client $client, Form43 $form43, Form621 $form621, Item $item, Unit $unit)
  {
    $this->client = $client;
    $this->form43 = $form43;
    $this->form621 = $form621;
    $this->item = $item;
    $this->unit = $unit;
  }

  // function call automatically from form43 page
  public function index(Request $request)
  {
    $user   = Auth::user();
    $search = trim((string) $request->input('search', ''));
    $termLike   = $search !== '' ? $search . '%' : null;

    $query = $this->form43->with(['client', 'item', 'additionsum'])
      ->select('id', 'client_id', 'item_id', 'last')
      ->when(!$this->hasRole(['admin']), fn($query) => $query->whereHas('client', fn($q) => $q->where('user_id', $user->id)))
      ->when($search !== '', function ($query) use ($termLike) {
        $query->where(function ($q) use ($termLike) {
          $q->whereHas('client', function ($query) use ($termLike) {
            $query->where(function ($q) use ($termLike) {
              $q->where('company_name', 'like', $termLike)
                ->orWhere('bin', 'like', $termLike)
                ->orWhere('mobile', 'like', $termLike)
                ->orWhereHas('user', fn($query) => $query->where('email', 'like', $termLike));
            });
          })->orWhereHas('item', function ($query) use ($termLike) {
            $query->where('name', 'like', $termLike)
              ->orWhere('hs_code', 'like', $termLike);
          });
        });
      })->addSelect(DB::raw("
        EXISTS (
            SELECT 1
            FROM form621s f
            WHERE f.lc_or_invoice_no   = form43s.lc_no
              AND f.client_id = form43s.client_id
              AND f.item_id   = form43s.item_id
              AND f.boe   = form43s.boe_no
        ) AS has_form621
    "))->orderBy('id', 'desc')
      ->paginate((int)request('size', 10))
      ->withQueryString();

    return Inertia("form43/index", [
      'form43' => $query,
      'search' => $search
    ]);
  }

  // function call from statiocode form43 for create
  public function store(Request $request)
  {
    $user = Auth::user();

    $formField = $request->validate(
      [
        'lc_no' => ['required', 'max:50', function ($attribute, $value, $fail) use ($request) {
          $exists = $this->form43->where([['lc_no', $value], ['boe_no', $request->boe_no], ['client_id', $request->client_id], ['item_id', $request->item_id]])->exists();

          if ($exists) {
            return $fail('Exists, change your input');
          }
        }],
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
        'purchase_unit_id' => ['required', 'numeric', 'gt:0', function ($attribute, $value, $fail) {
          $unit = $this->unit->find($value);
          if (!$unit) return $fail('Not exists');
        }],
        'sale_unit_id' => ['required', 'numeric', 'gt:0', function ($attribute, $value, $fail) {
          $unit = $this->unit->find($value);
          if (!$unit) return $fail('Not exists');
        }],
        'factor' => 'required|numeric|gt:0',
        'item_code' => ['required', 'numeric', 'gt:0', function ($attribute, $value, $fail) use ($request) {
          $clientId = $request->input('client_id');
          $itemId   = $request->input('item_id');
          $rowId    = $request->input('id'); // null on create, set on update

          if (!$clientId || !$itemId) {
            return $fail('Client, item required');
          }

          // Base query for this client
          $base = $this->form43->where('client_id', $clientId);

          // 1) Existing code for THIS item (same client+item, other rows ignored)
          $existingCode = (clone $base)->when($rowId, fn($q) => $q->where('id', '!=', $rowId))
            ->where('item_id', $itemId)
            ->value('item_code');

          if ($existingCode !== null) {
            // Must match existing code
            if ((string) $existingCode !== (string) $value) {
              return $fail("Not matched ({$existingCode})");
            }
            return; // matched or failed; in both cases, no more checks
          }

          // 2) No code yet for this item → code must be unique among other items of same client
          $usedByAnother = (clone $base)->when($rowId, fn($q) => $q->where('id', '!=', $rowId))
            ->where('item_id', '!=', $itemId)
            ->where('item_code', $value)
            ->exists();

          if ($usedByAnother) {
            return $fail('Already used');
          }
        }],
        'submission_date' => ['required', 'date_format:"Y-m-d"', function ($attribute, $value, $fail) {
          $inputdate = Carbon::parse($value, 'Asia/Dhaka')->startOfDay();
          $currentdate = Carbon::now('Asia/Dhaka')->startOfDay();

          if (!$inputdate->isSameDay($currentdate)) {
            return $fail("Select current date");
          }
        }],
        'first_delivery_date' => ['nullable', 'date_format:"Y-m-d"', function ($attribute, $value, $fail) {
          $date = Carbon::parse($value, 'Asia/Dhaka')->startOfDay();
          $today = Carbon::today('Asia/Dhaka')->startOfDay();

          // Stop if entry date is over current date (future date)
          if ($date->gt($today)) {
            return $fail("Greater than today");
          }
        }],
        'boe_date' => ['required', 'date_format:"Y-m-d"', function ($attribute, $value, $fail) {
          $date = Carbon::parse($value, 'Asia/Dhaka')->startOfDay();
          $today = Carbon::today('Asia/Dhaka')->startOfDay();

          // Stop if entry date is over current date (future date)
          if ($date->gt($today)) {
            return $fail("Greater than today");
          }
        }],
        'boe_no' => 'required|max:20',
        'mcu' => 'required|numeric|gte:0',
        'quantity' => 'required|numeric|gt:0',
        'wastage' => 'required|numeric|gte:0|decimal:0,2|between:0,100.00',
        'excess_quantity' => 'required|numeric|gte:0',
        'base_value' => 'required|numeric|gt:0',
      ],
      [
        'lc_no.required' => 'Lc No required',
        'lc_no.max' => 'Max 50 char',
        'client_id.required' => 'BIN required',
        'client_id.numeric' => 'Number only',
        'client_id.gt' => 'Number > 0',
        'item_id.required' => 'Item required',
        'item_id.numeric' => 'Number only',
        'item_id.gt' => 'Number > 0',
        'purchase_unit_id.required' => 'Unit required',
        'purchase_unit_id.numeric' => 'Number only',
        'purchase_unit_id.gt' => 'Number > 0',
        'sale_unit_id.required' => 'Unit required',
        'sale_unit_id.numeric' => 'Number only',
        'sale_unit_id.gt' => 'Number > 0',
        'factor.required' => 'Factor required',
        'factor.numeric' => 'Nnumber only',
        'factor.gt' => 'Nnumber > 0',
        'item_code.required' => 'Item code required',
        'item_code.numeric' => 'Nnumber only',
        'item_code.gt' => 'Nnumber > 0',
        'submission_date.required' => 'Submission date required',
        'submission_date.date_format' => 'Invalid date',
        'first_delivery_date.date_format' => 'Invalid date',
        'boe_date.required' => 'BoE/Invoice date required',
        'boe_date.date_format' => 'Invalid date',
        'boe_no.required' => 'BoE or Invoice no required',
        'boe_no.max' => 'Max 20 char',
        'mcu.required' => 'MCU required',
        'mcu.numeric' => 'Number only',
        'mcu.gte' => '(+)Number  only',
        'quantity.required' => 'Quantity required',
        'quantity.numeric' => 'Number only',
        'quantity.gt' => 'Number > 0',
        'wastage.required' => 'Wastage required',
        'wastage.numeric' => 'Number only',
        'wastage.gte' => '(+)Number only',
        'wastage.between' => '0 to 100.00',
        'excess_quantity.required' => 'Excess quantity required',
        'excess_quantity.numeric' => 'Number only',
        'excess_quantity.gte' => 'Number >= 0',
        'base_value.required' => 'Base value required',
        'base_value.numeric' => 'Number only',
        'base_value.gt' => 'Number > 0',
      ]
    );

    try {
      DB::transaction(function () use ($formField, $request) {
        $qty = $request->input('quantity');
        $excess = $request->input('excess_quantity');
        $wastage = $request->input('wastage');
        $factor = $request->input('factor');
        $base = $request->input('base_value');

        // purchase unit total
        $purchase_unit_total = $qty + $excess;
        $net_quantity = $wastage ? ($purchase_unit_total - (($purchase_unit_total * $wastage) / 100)) : $purchase_unit_total;
        $sale_unit_total = $net_quantity * $factor;
        $unit_price = ($base && $sale_unit_total) ? ($base / ($net_quantity * $factor)) : 0;

        $formField['purchase_unit_total'] = number_format($purchase_unit_total, 2, ".", "");
        $formField['sale_unit_total'] = number_format($sale_unit_total, 2, ".", "");
        $formField['unit_price'] = number_format($unit_price, 2, ".", "");

        // last form43 id find out with client_id, item_id
        $last = $this->form43->where(['client_id' => $request->client_id, 'item_id' => $request->item_id])->groupBy(['client_id', 'item_id'])->max('id');
        // last was 1. in this case update last will be update 0 then created last will be 1
        !empty($this->form43) && $this->form43->where('id', $last)->update(['last' => 0]);
        // last will be one
        $formField['last'] = 1;

        $this->form43->create($formField);
      });

      // realtime data
      event(new CommonEvent($user->id));

      return back()->with('message', 'Form 4.3 created');
    } catch (Throwable $e) {
      return back()->with("error", $e->getMessage());
    }
  }

  // function call from form43 page as api for same page
  public function show($id)
  {
    $user    = Auth::user();

    $form43 = $this->form43->with(['client.division', 'client.user.role', 'item', 'purchaseunit', 'saleunit', 'additions', 'additions.costsheet', 'additionsum'])->find($id);

    if (!$form43) {
      return response()->json(['form43' => null, 'max_item_code' => null, 'has_form621' => false, 'error' => 'Form 4.3 not found.']);
    }

    if (!$this->hasRole(['admin']) && ($form43->client?->user_id !== $user->id)) {
      return response()->json(['form43' => null, 'max_item_code' => null, 'has_form621' => false, 'error' => 'Unauthorised']);
    }

    $maxItemCode = $this->form43->where('client_id', $form43->client_id)->max('item_code');

    $hasForm621 = $this->form621->where('form43_id', $form43->id)->exists();

    return response()->json(['form43' => $form43, 'max_item_code' => $maxItemCode, 'has_form621' => $hasForm621, 'error' => null]);
  }

  // function call form form43 page for update
  public function update(Request $request, $id)
  {
    $user = Auth::user();

    if ($this->form621->where('form43_id', $id)->exists()) {
      return back()->with('error', 'Cannot update: used in 6.2.1');
    }

    $formField = $request->validate(
      [
        'lc_no' => ['required', 'max:50', function ($attribute, $value, $fail) use ($request) {
          $exists = $this->form43->where([['lc_no', $value], ['boe_no', $request->boe_no], ['client_id', $request->client_id], ['item_id', $request->item_id]])->exists();

          if ($exists) {
            return $fail('Exists, change your input');
          }
        }],
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
        'purchase_unit_id' => ['required', 'numeric', 'gt:0', function ($attribute, $value, $fail) {
          $unit = $this->unit->find($value);
          if (!$unit) return $fail('Not exists');
        }],
        'sale_unit_id' => ['required', 'numeric', 'gt:0', function ($attribute, $value, $fail) {
          $unit = $this->unit->find($value);
          if (!$unit) return $fail('Not exists');
        }],
        'factor' => 'required|numeric|gt:0',
        'item_code' => ['required', 'numeric', 'gt:0', function ($attribute, $value, $fail) use ($request) {
          $clientId = $request->input('client_id');
          $itemId   = $request->input('item_id');
          $rowId    = $request->input('id'); // null on create, set on update

          if (!$clientId || !$itemId) {
            return $fail('Client, item required');
          }

          // Base query for this client
          $base = $this->form43->where('client_id', $clientId);

          // 1) Existing code for THIS item (same client+item, other rows ignored)
          $existingCode = (clone $base)->when($rowId, fn($q) => $q->where('id', '!=', $rowId))
            ->where('item_id', $itemId)
            ->value('item_code');

          if ($existingCode !== null) {
            // Must match existing code
            if ((string) $existingCode !== (string) $value) {
              return $fail("Not matched ({$existingCode})");
            }
            return; // matched or failed; in both cases, no more checks
          }

          // 2) No code yet for this item → code must be unique among other items of same client
          $usedByAnother = (clone $base)->when($rowId, fn($q) => $q->where('id', '!=', $rowId))
            ->where('item_id', '!=', $itemId)
            ->where('item_code', $value)
            ->exists();

          if ($usedByAnother) {
            return $fail('Already used');
          }
        }],
        'submission_date' => ['required', 'date_format:"Y-m-d"', function ($attribute, $value, $fail) {
          $inputdate = Carbon::parse($value, 'Asia/Dhaka')->startOfDay();
          $currentdate = Carbon::now('Asia/Dhaka')->startOfDay();

          if (!$inputdate->isSameDay($currentdate)) {
            return $fail("Select current date");
          }
        }],
        'first_delivery_date' => ['nullable', 'date_format:"Y-m-d"', function ($attribute, $value, $fail) {
          $date = Carbon::parse($value, 'Asia/Dhaka')->startOfDay();
          $today = Carbon::today('Asia/Dhaka')->startOfDay();

          // Stop if entry date is over current date (future date)
          if ($date->gt($today)) {
            return $fail("Greater than today");
          }
        }],
        'boe_date' => ['required', 'date_format:"Y-m-d"', function ($attribute, $value, $fail) {
          $date = Carbon::parse($value, 'Asia/Dhaka')->startOfDay();
          $today = Carbon::today('Asia/Dhaka')->startOfDay();

          // Stop if entry date is over current date (future date)
          if ($date->gt($today)) {
            return $fail("Greater than today");
          }
        }],
        'boe_no' => 'required|max:20',
        'mcu' => 'required|numeric|gte:0',
        'quantity' => 'required|numeric|gt:0',
        'wastage' => 'required|numeric|gte:0|decimal:0,2|between:0,100.00',
        'excess_quantity' => 'required|numeric|gte:0',
        'base_value' => 'required|numeric|gt:0',
      ],
      [
        'lc_no.required' => 'Lc No required',
        'lc_no.max' => 'Max 50 char',
        'client_id.required' => 'BIN required',
        'client_id.numeric' => 'Number only',
        'client_id.gt' => 'Number > 0',
        'item_id.required' => 'Item required',
        'item_id.numeric' => 'Number only',
        'item_id.gt' => 'Number > 0',
        'purchase_unit_id.required' => 'Unit required',
        'purchase_unit_id.numeric' => 'Number only',
        'purchase_unit_id.gt' => 'Number > 0',
        'sale_unit_id.required' => 'Unit required',
        'sale_unit_id.numeric' => 'Number only',
        'sale_unit_id.gt' => 'Number > 0',
        'factor.required' => 'Factor required',
        'factor.numeric' => 'Nnumber only',
        'factor.gt' => 'Nnumber > 0',
        'item_code.required' => 'Item code required',
        'item_code.numeric' => 'Nnumber only',
        'item_code.gt' => 'Nnumber > 0',
        'submission_date.required' => 'Submission date required',
        'submission_date.date_format' => 'Invalid date',
        'first_delivery_date.date_format' => 'Invalid date',
        'boe_date.required' => 'BoE/Invoice date required',
        'boe_date.date_format' => 'Invalid date',
        'boe_no.required' => 'BoE or Invoice no required',
        'boe_no.max' => 'Max 20 char',
        'mcu.required' => 'MCU required',
        'mcu.numeric' => 'Number only',
        'mcu.gte' => '(+)Number only',
        'quantity.required' => 'Quantity required',
        'quantity.numeric' => 'Number only',
        'quantity.gt' => 'Number > 0',
        'wastage.required' => 'Wastage required',
        'wastage.numeric' => 'Number only',
        'wastage.gte' => '(+)Number only',
        'wastage.between' => '0 to 100.00',
        'excess_quantity.required' => 'Excess quantity required',
        'excess_quantity.numeric' => 'Number only',
        'excess_quantity.gte' => 'Number >= 0',
        'base_value.required' => 'Base value required',
        'base_value.numeric' => 'Number only',
        'base_value.gt' => 'Number > 0',
      ]
    );

    try {
      DB::transaction(function () use ($formField, $request, $id) {
        $qty = $request->input('quantity');
        $excess = $request->input('excess_quantity');
        $wastage = $request->input('wastage');
        $factor = $request->input('factor');
        $base = $request->input('base_value');

        // unit total and unit price
        $purchase_unit_total = $qty + $excess;
        $net_quantity = $wastage ? ($purchase_unit_total - (($purchase_unit_total * $wastage) / 100)) : $purchase_unit_total;
        $sale_unit_total = $net_quantity * $factor;
        $unit_price = ($base && $sale_unit_total) ? ($base / ($net_quantity * $factor)) : 0;

        $formField['purchase_unit_total'] = number_format($purchase_unit_total, 2, ".", "");
        $formField['sale_unit_total'] = number_format($sale_unit_total, 2, ".", "");
        $formField['unit_price'] = number_format($unit_price, 2, ".", "");

        $this->form43->where('id', $id)->update($formField);
      });

      // realtime data
      event(new CommonEvent($user->id));

      return back()->with('message', 'Form 4.3 updated');
    } catch (Throwable $e) {
      return back()->with("error", $e->getMessage());
    }
  }

  // function call from form43 page for delete
  public function destroy($id)
  {
    $user    = Auth::user();

    // 1) Load record without user filter first
    $form43 = $this->form43->with('client')->find($id);

    if (!$form43) {
      return back()->with('error', 'Form 4.3 not found');
    }

    // Case 2: exists but not his and not admin
    if (!$this->hasRole(['admin']) && ($form43->client?->user_id !== $user->id)) {
      return back()->with('error', 'Unauthorised');
    }

    // 3) Hard block if used in 6.2.1
    $inUse = $this->form621->where('form43_id', $form43->id)->exists();

    if ($inUse) {
      return back()->with('error', 'Cannot delete: Already used in Form 6.2.1');
    }

    // // can delete last combination only
    // if ($form43->last != 1) {
    //   return back()->with('error', 'Can not delete: not last combination');
    // }

    // 4) Delete + maintain "last" in a transaction
    DB::transaction(function () use ($user, $form43) {
      $clientId = $form43->client_id;
      $itemId   = $form43->item_id;

      // delete current row
      $form43->delete();

      // set latest remaining record as last=1 (if any)
      $newLastId = $this->form43->where('client_id', $clientId)->where('item_id', $itemId)->max('id'); // null if none left

      if ($newLastId) {
        $this->form43->where('id', $newLastId)->update(['last' => 1]);
      }
    });

    // realtime data
    event(new CommonEvent($user->id));

    return back()->with('message', 'Form 4.3 deleted');
  }

  /**
   * Find last from Form 4.3
   * combination will be: client, item, last
   */
  public function form43Last(Request $request)
  {
    $user    = Auth::user();

    $client  = $request->query('client_id');
    $item    = $request->query('item_id');

    // max item code (keep this, works even if there is no "last")
    $maxItemCode = $this->form43->when(!$this->hasRole(['admin']), fn($q) => $q->whereHas('client', fn($c) => $c->where('user_id', $user->id)))
      ->where('client_id', $client)
      ->max('item_code');

    // last 4.3 for this client+item
    $form43 = $this->form43->with(['client', 'item', 'purchaseunit:id,name', 'saleunit:id,name', 'additions', 'additionsum'])
      ->when(!$this->hasRole(['admin']), fn($query) => $query->whereHas('client', fn($q) => $q->where('user_id', $user->id)))
      ->where('client_id', $client)
      ->where('item_id', $item)
      ->where('last', 1)
      ->first();

    if (!$form43) {
      return response()->json(['form43last' => $form43, 'unitprice' => null, 'max_item_code' => $maxItemCode, 'has_addition' => false, 'message' => '4.3 not found']);
    }

    $additionSum = optional($form43->additionsum())->first();
    $unitPrice = optional($form43)->unit_price;
    $form43ForFactor = ($unitPrice + optional($additionSum)->valuesum);

    // use your simple additions() relation here
    $hasAddition = $form43->additions()->exists();

    if (!$hasAddition) {
      return response()->json(['form43last' => $form43, 'unitprice' => null, 'max_item_code' => $maxItemCode, 'has_addition' => $hasAddition, 'message' => '4.3 has no addition']);
    }

    return response()->json([
      'form43last' => $form43,
      'unitprice' => number_format($form43ForFactor, 2, '.', ''),
      'max_item_code' => $maxItemCode,
      'has_addition' => $hasAddition,
      'message' => null
    ]);
  }
}
