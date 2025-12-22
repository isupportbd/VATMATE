<?php

namespace Modules\Purchase\Http\Controllers;

use App\Events\CommonEvent;
use App\Http\Controllers\Controller;
use App\Traits\ChecksUserRole;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Modules\Client\Models\Client;
use Modules\Form43\Models\Form43;
use Modules\Form621\Models\Form621;
use Modules\Item\Models\Item;
use Modules\Mushaknote\Models\Mushaknote;
use Modules\Purchase\Models\Purchase;
use Modules\Stationcode\Models\Stationcode;
use Modules\Unit\Models\Unit;
use Modules\Vatfactor\Models\Vatfactor;
use Throwable;

class PurchaseController extends Controller
{
  use ChecksUserRole;

  public $client;
  public $form43;
  public $form621;
  public $purchase;
  public $item;
  public $mushaknote;
  public $stationcode;
  public $unit;
  public $vatfactor;

  public function __construct(Client $client, Form43 $form43, Purchase $purchase, Item $item, Mushaknote $mushaknote, Stationcode $stationcode, Unit $unit, Vatfactor $vatfactor, Form621 $form621)
  {
    $this->client = $client;
    $this->form43 = $form43;
    $this->form621 = $form621;
    $this->purchase = $purchase;
    $this->item = $item;
    $this->mushaknote = $mushaknote;
    $this->stationcode = $stationcode;
    $this->unit = $unit;
    $this->vatfactor = $vatfactor;
  }

  // function call automatically from importer purchase page
  public function index(Request $request)
  {
    $user = Auth::user();
    $search = trim((string) $request->input('search', ''));
    $termLike   = $search !== '' ? $search . '%' : null;

    $purchase = $this->purchase->with(['client', 'client.division', 'client.user', 'client.user.role', 'stationcode', 'item', 'mushaknote', 'unit'])
      ->when(!$this->hasRole(['admin']), fn($query) => $query->whereHas('client', fn($q) => $q->where('user_id', $user->id)))
      ->when($search !== '', function ($query) use ($termLike) {
        $query->where(function ($query) use ($termLike) {
          $query->whereHas('client', function ($query) use ($termLike) {
            $query->where('bin', 'like', $termLike)
              ->orWhere('company_name', 'like', $termLike)
              ->orWhere('mobile', 'like', $termLike);
          })->orWhereHas('item', fn($query) => $query->where('name', 'like', $termLike));
        });
      });

    $result = $purchase->orderBy('id', 'desc')->paginate((int)request('size', 10))->withQueryString();

    return Inertia("purchase/index", ['purchase' => $result, 'search' => $search]);
  }

  // function call from importer purchase page for create
  public function store(Request $request)
  {
    $user = Auth::user();

    // calculation part for validation and insert data
    $av      = (float) $request->input('assessable_value');
    $cd      = (float) $request->input('cd', 0);
    $rd      = (float) $request->input('rd', 0);
    $sd      = (float) $request->input('sd', 0);
    $vat     = (float) $request->input('vat', 0);
    $at      = (float) $request->input('at', 0);
    $mcu     = (float) $request->input('mcu', 0);
    $qty     = (float) $request->input('quantity');
    $exQty   = (float) $request->input('excess_quantity', 0);
    $wastage = (float) $request->input('wastage', 0);

    $cdValue   = $av * ($cd / 100);
    $rdValue   = $av * ($rd / 100);
    $sdValue   = ($av + $cdValue + $rdValue) * ($sd / 100);
    $baseValue = $av + $cdValue + $rdValue + $sdValue;
    $vatValue  = $baseValue * ($vat / 100);
    $atValue   = $baseValue * ($at / 100);
    $totalQuantity = ($qty + $exQty) - (($qty + $exQty) * ($wastage / 100));

    // NEW: fetch Form 4.3 to get factor BEFORE validation (to compute converted net qty)
    $form43ForFactor = $this->form43->where([
      ['client_id', $request->input('client_id')],
      ['item_id',   $request->input('item_id')],
      ['last', 1]
    ])->first();
    $factor = (float) ($form43ForFactor->factor ?? 1);

    // NEW: converted quantities in sale unit (for validation + insert)
    $qtyConv            = (float) $qty   * $factor;
    $exQtyConv          = (float) $exQty * $factor;
    $totalQuantityConv  = (float) $totalQuantity * $factor;
    $unitPriceConv      = ($totalQuantityConv > 0) ? ($baseValue / $totalQuantityConv) : null;
    $mcuAmount          = (float) $av * $mcu;

    $formField = $request->validate(
      [
        'client_id' => ['required', 'numeric', 'gt:0', function ($attribute, $value, $fail) use ($user) {
          $client = $this->client->find($value);
          if (!$client) return $fail('Client does not exist');
          if ($client->status == 0) return $fail('BIN inactive');
          if (!$this->hasRole(['admin']) && $client->user_id !== $user->id) return $fail('Unauthorised');
        }],
        'item_id' => ['required', 'integer', 'gt:0', 'bail', Rule::exists('items', 'id'), function ($attribute, $value, $fail) use ($request) {
          $clientId = $request->input('client_id');

          $item = $this->item->find($value);
          if (!$item) return $fail('Item not exists');

          $form43 = $this->form43->where('client_id', $clientId)->where('item_id', $value)->where('last', 1)->first();

          if (!$form43) return $fail('4.3 not found');
          if (!$form43->additions()->exists()) return $fail('4.3 has no addition');
        }],
        'lc_no' => ['required', 'max:50', function ($attribute, $value, $fail) use ($request) {
          $purchase = $this->purchase->where('lc_no', $value)->first();

          if ($purchase && ($purchase->client_id != $request->client_id)) return $fail("Not same client");
        }],
        'lc_date' => ['nullable', 'date_format:"Y-m-d"', function ($attribute, $value, $fail) {
          $entrydate = Carbon::parse($value, 'Asia/Dhaka')->startOfDay();
          $today   = Carbon::today('Asia/Dhaka')->startOfDay();

          // Stop if entry date is over current date (future date)
          if ($entrydate->gt($today)) return $fail("Greater than today");
        }],
        'boe' => ['required', 'max:10', function ($attribute, $value, $fail) use ($request) {
          $dup = $this->purchase->where([
            ['boe', $value],
            ['item_id', $request->input('item_id')],
            ['boe_date', $request->input('boe_date')],
            ['stationcode_id', $request->input('stationcode_id')]
          ])->exists();

          if ($dup) return $fail('Exists, change your input');
        }],
        'boe_date' => ['required', 'date_format:"Y-m-d"', function ($attribute, $value, $fail) use ($request) {
          $entrydate = Carbon::parse($request->input('entry_date'), "Asia/Dhaka")->startOfDay();
          $boedate = Carbon::parse($value, 'Asia/Dhaka')->startOfDay();
          $today   = Carbon::today('Asia/Dhaka')->startOfDay();

          // Stop if entry date is over current date (future date)
          if ($boedate->gt($today)) return $fail("Greater than today");

          // error show if boe date is big from entry date
          if ($boedate->gt($entrydate)) return $fail("BoE date > Entry date");
        }],
        'entry_date' => ['required', 'bail', 'date_format:"Y-m-d"', 'bail', function ($attribute, $value, $fail) use ($request) {
          $entrydate = Carbon::parse($value, 'Asia/Dhaka')->startOfDay();
          $today   = Carbon::today('Asia/Dhaka')->startOfDay();

          // Stop if entry date is over current date (future date)
          if ($entrydate->gt($today)) return $fail("Greater than today");

          $lastDate = $this->form621->where('client_id', $request->input('client_id'))->groupBy(['client_id'])->max('date');
          if ($lastDate && $lastDate > $value) return $fail("Violate last date");
        }],
        'mushaknote_id' => ['required', 'numeric', 'gt:0', 'bail', function ($attribute, $value, $fail) use ($request) {
          // find note id from mushaknote
          $note15 = $this->mushaknote->where('name_note_no', 'note_15')->value('id');
          $gap = $this->vatfactor->where('name', 'month_gap')->value('value');

          // Only run gap + receipt checks when note_15 is selected
          if ($note15 && $note15 == $value) {

            // Only run date gap check when both dates are present
            if ($request->boe_date && $request->entry_date) {

              $boe   = Carbon::parse($request->input('boe_date'), 'Asia/Dhaka');
              $entry = Carbon::parse($request->input('entry_date'), 'Asia/Dhaka');

              $boeIndex   = $boe->year * 12 + $boe->month;
              $entryIndex = $entry->year * 12 + $entry->month;
              $monthGap   = ($entryIndex - $boeIndex) + 1;

              if ($monthGap > $gap) return $fail('Over ' . $gap . ' tax period');
            }
          }

          if ($note15 && ($note15 == $value) && (empty($request->input('receipt_number')) || empty($request->input('receipt_date')))) {
            return $fail('Need receipt number and date');
          }

          // check exists or not
          if (!$this->mushaknote->where('id', $value)->exists()) return $fail('Not exists');
        }],
        'stationcode_id' => ['required', 'numeric', 'gt:0', function ($attribute, $value, $fail) use ($request) {
          if (!$this->stationcode->where('id', $value)->exists()) return $fail('Not exists');
        }],
        'df_cvat_cf' => 'required|numeric|gte:0',
        'assessable_value' => 'required|numeric|gt:0',
        'quantity' => 'required|numeric|gt:0',
        'excess_quantity' => 'required|numeric|gte:0',
        'wastage' => 'required|numeric|gte:0|decimal:0,2|between:0,100.00',
        'unit_id' => ['required', 'numeric', 'gt:0', function ($attribute, $value, $fail) use ($request) {
          // last form43 id find out with client_id, item_id
          $unitId = $this->form43->where([['client_id', $request->input('client_id')], ['item_id', $request->input('item_id')], ['last', 1]])->value('sale_unit_id');

          // error show if not match form43
          if (empty($unitId) || (int)$unitId !== (int)$value) return $fail("Not match");

          // check exists or not
          if (!$this->unit->where('id', $value)->exists()) return $fail('Not exists');
        }],
        'base_value' => 'nullable|numeric|gt:0',
        // NEW: validate against converted unit price (based on converted net qty)
        'unit_price'  => ['nullable', 'numeric', 'gt:0', 'bail', function ($attribute, $value, $fail) use ($request, $unitPriceConv, $form43ForFactor) {
          if ($unitPriceConv === null || $unitPriceConv <= 0) return $fail('Computed unit price invalid');

          $form43UnitPrice = $form43ForFactor?->unit_price ?? $this->form43->where([['client_id', $request->input('client_id')], ['item_id', $request->input('item_id')], ['last', 1]])->value('unit_price');

          $calc = !empty($form43UnitPrice) ? ((($unitPriceConv - $form43UnitPrice) / $form43UnitPrice) * 100) : null;

          $vatfactor = $this->vatfactor->where('name', 'vatfact')->value('value');

          if (!empty($form43UnitPrice) && $vatfactor !== null) {
            if ($calc >  (float)$vatfactor) return $fail('Over ' . $vatfactor . ' (' . number_format($calc, 2, '.', '') . '%)');
            if ($calc < -(float)$vatfactor) return $fail('Less ' . $vatfactor . ' (' . number_format($calc, 2, '.', '') . '%)');
          }
        }],

        'cd' => 'required|numeric|gte:0',
        'rd' => 'required|numeric|gte:0',
        'sd' => 'required|numeric|gte:0',
        'vat' => ['required', 'numeric', 'gte:0', 'between:0,15.00', function ($attribute, $value, $fail) use ($request) {
          // get note_15 id
          $note15 = $this->mushaknote->where('name_note_no', 'note_15')->value('id');

          // If mushaknote_id = note_15 → VAT MUST be 15
          if ($request->mushaknote_id == $note15 && $value != 15) {
            return $fail('Set 15%');
          }
        }],
        'at' => ['required', 'numeric', 'gte:0', function ($attribute, $value, $fail) use ($request) {
          // Skip checks until BOTH dates are filled
          if (!$request->boe_date || !$request->entry_date) return;

          $boe   = Carbon::parse($request->boe_date, 'Asia/Dhaka');
          $entry = Carbon::parse($request->entry_date, 'Asia/Dhaka');

          $boeIndex   = $boe->year * 12 + $boe->month;
          $entryIndex = $entry->year * 12 + $entry->month;
          $monthGap   = ($entryIndex - $boeIndex) + 1;

          $gap = $this->vatfactor->where('name', 'month_gap')->value('value');

          // Over X months → AT must be 0
          if ($monthGap > $gap && $value > 0) return $fail("Set 0%, Over " . $gap . " months");

          // max AT check
          $vatfactor = $this->vatfactor->where('name', 'atfact')->value('value');

          if ($value > $vatfactor) return $fail('Range 0 to (' . number_format($vatfactor, 2, '.', '') . '%)');
        }],
        'opening_stock' => [Rule::requiredIf(function () use ($request) {
          return !$this->form621->where([['client_id', $request->input('client_id')], ['item_id',  $request->input('item_id')]])
            ->exists();
        }), 'numeric', 'gte:0'],
        'mcu' => 'required|numeric|gte:0',
        'mcu_amount' => 'nullable|numeric|gte:0',
        'receipt_number' => 'nullable|max:20',
        'receipt_date' => ['nullable', 'date_format:"Y-m-d"', function ($attribute, $value, $fail) use ($request) {
          $date = Carbon::parse($value, 'Asia/Dhaka')->startOfDay();
          $today   = Carbon::today('Asia/Dhaka')->startOfDay();

          // Stop if entry date is over current date (future date)
          if ($date->gt($today)) {
            return $fail("Greater than today");
          }
        }],
        'exporter_name' => 'nullable|max:100',
        'address' => 'nullable|max:100',
      ],
      [
        'client_id.required' => 'BIN required',
        'client_id.numeric' => 'Number only',
        'client_id.gt' => 'Number >0',
        'item_id.required' =>  'Item required',
        'item_id.numeric' =>  'Number only',
        'item_id.gt' =>  'Number > 0',
        'lc_no.required' => 'LC number required',
        'lc_no.max' => 'Max 50 char',
        'lc_date.date_format' =>  'Invalid date',
        'boe.required' =>  'BoE required',
        'boe.max' =>  'Max 10 char',
        'boe_date.required' =>  'BoE date required',
        'boe_date.date_format' =>  'Invalid date',
        'entry_date.required' =>  'Entry date required',
        'entry_date.date_format' =>  'Invalid date',
        'mushaknote_id.required' =>  'Mushak note required',
        'mushaknote_id.numeric' =>  'Number only',
        'mushaknote_id.gt' =>  'Number > 0',
        'stationcode_id.required' => 'Station code required',
        'stationcode_id.numeric' => 'Number only',
        'stationcode_id.gt' => 'Number > 0',
        'df_cvat_cf.required' =>  'Df/CVAT/VF required',
        'df_cvat_cf.numeric' =>  'Number only',
        'df_cvat_cf.gte' =>  '(+)Number only',
        'assessable_value.required' =>  'Assessable value required',
        'assessable_value.numeric' =>  'Number only',
        'assessable_value.gt' =>  'Number > 0',
        'quantity.required' =>  'Quantity required',
        'quantity.numeric' =>  'Number only',
        'quantity.gt' =>  'Number > 0',
        'excess_quantity.required' =>  'Excess quantity required',
        'excess_quantity.numeric' =>  'Number only',
        'excess_quantity.gt' =>  'Number > 0',
        'wastage.required' =>  'Wastage required',
        'wastage.numeric' =>  'Number only',
        'wastage.gte' =>  '(+)Number only',
        'wastage.between' =>  '0 to 100.00',
        'unit_id.required' =>  'Unit required',
        'unit_id.numeric' =>  'Number only',
        'unit_id.gt' =>  'Number > 0',
        'base_value.numeric' =>  'Number only',
        'base_value.gt' =>  'Number > 0',
        'unit_price.numeric' =>  'Number only',
        'unit_price.gt' =>  'Number > 0',
        'net_quantity.required' =>  'Net quantity required',
        'net_quantity.numeric' =>  'Number only',
        'net_quantity.gt' =>  '(+)Number only',
        'cd.required' =>  'CD required',
        'cd.numeric' =>  'Number only',
        'cd.gte' =>  '(+)Number only',
        'rd.required' =>  'RD required',
        'rd.numeric' =>  'Number only',
        'rd.gte' =>  '(+)Number only',
        'sd.required' =>  'SD required',
        'sd.numeric' =>  'Number only',
        'sd.gte' =>  '(+)Number only',
        'vat.required' =>  'VAT required',
        'vat.numeric' =>  'Number only',
        'vat.gte' =>  '(+)Number only',
        'vat.between' =>  'Range 0 to 15',
        'at.required' =>  'AT required',
        'at.numeric' =>  'Number only',
        'at.gte' =>  '(+)Number only',
        'at.between' =>  'Range 0 to 7.50',
        'opening_stock.required' => 'Opening stock required',
        'opening_stock.numeric' =>  'Number only',
        'opening_stock.gte' =>  '(+)Number only',
        'mcu.required' =>  'MCU required',
        'mcu.numeric' =>  'Number only',
        'mcu.gte' =>  '(+)Number only',
        'mcu_amount.numeric' =>  'Number only',
        'mcu_amount.gte' =>  '(+)Number only',
        'receipt_number.max' => 'Max 20 char',
        'receipt_date.date_format' => 'Invalid date',
        'exporter_name' => 'Max 100 char',
        'address.max' => 'Max 100 char',
      ]
    );

    // use transaction for those exception
    try {
      DB::transaction(function () use ($formField, $request, $user, $cdValue, $rdValue, $sdValue, $baseValue, $vatValue, $atValue, $form43ForFactor, $qtyConv, $exQtyConv, $totalQuantityConv, $unitPriceConv, $mcuAmount) {
        // NEW: set purchase unit_id to sale unit (since quantities are converted)
        $form43 = $form43ForFactor ?: $this->form43->where([['client_id', $request->input('client_id')], ['item_id',   $request->input('item_id')], ['last', 1]])->first();

        if (!$form43) {
          throw new \RuntimeException('Form 4.3 not found for conversion.');
        }

        // lock last form621 row for this client+item
        $lastRow = $this->form621
          ->where('client_id', $request->input('client_id'))
          ->where('item_id',   $request->input('item_id'))
          ->lockForUpdate()
          ->latest('id')
          ->first();

        // NEW: opening & stock in converted (sale unit)
        $openingInput = (float) $request->input('opening_stock', 0);
        $opening = $lastRow ? (float) $lastRow->stock : (float) $openingInput;
        $quantity = (float) $totalQuantityConv;
        $stock    = $opening + $quantity;

        $formField['cd_value']        = number_format($cdValue, 2, ".", "");
        $formField['rd_value']        = number_format($rdValue, 2, ".", "");
        $formField['sd_value']        = number_format($sdValue, 2, ".", "");
        $formField['vat_value']       = number_format($vatValue, 2, ".", "");
        $formField['at_value']        = number_format($atValue, 2, ".", "");
        $formField['base_value']      = number_format($baseValue, 2, ".", "");

        // NEW: override purchase with CONVERTED (sale unit) values
        $formField['quantity']        = number_format($qtyConv,           2, ".", "");
        $formField['excess_quantity'] = number_format($exQtyConv,         2, ".", "");
        $formField['total_quantity']  = number_format($totalQuantityConv, 2, ".", "");
        $formField['mcu_amount']      = number_format($mcuAmount,     2, ".", "");
        $formField['unit_price']      = number_format($unitPriceConv,     2, ".", "");
        $formField['unit_id']         = $form43->sale_unit_id;
        $formField['factor']          = $form43->factor;
        $formField['stock']           = number_format($stock, 2, ".", "");

        // create purchase
        $purchase = $this->purchase->create($formField);

        if (!$purchase) return;

        if ($lastRow) {
          $lastRow->update(['last' => 0]);
        }

        // insert into form621 (converted, sale unit)
        $purchase->form621()->create([
          'client_id'        => $formField['client_id'],
          'item_id'          => $formField['item_id'],
          'form43_id'        => $form43->id,
          'lc_or_invoice_no' => $formField['lc_no'],
          'boe'              => $formField['boe'],
          'quantity'         => number_format($quantity, 2, ".", ""),                 // converted
          'unit_id'          => $form43->sale_unit_id,     // sale unit
          'opening_stock'    => number_format($opening, 2, ".", ""),                  // converted
          'stock'            => number_format($stock, 2, ".", ""),                    // converted
          'date'             => $formField['entry_date'],
          'last'             => 1,
          'userid'           => $user->id,
        ]);
      });

      // find the client to get owner user_id
      $client    = $this->client->find($formField['client_id']);
      $actorId   = $user->id;                  // who performed the action (user/admin)
      $targetId  = $client->user_id ?? $user->id; // whose data this belongs to

      // realtime data
      event(new CommonEvent($user->id));

      return back()->with("message", 'Purchase created');
    } catch (Throwable $e) {
      return back()->with("error", $e->getMessage());
    }
  }


  // function call from importer purchase page as api for same page
  public function show($id)
  {
    $user    = Auth::user();

    $purchase = $this->purchase->with(['client.division', 'client.user.role', 'stationcode', 'item', 'mushaknote', 'unit'])->find($id);

    if (!$this->hasRole(['admin']) && $purchase && ($purchase->client->user_id !== $user->id)) {
      return response()->json(['purchase' => null, 'error' => 'Unauthorized']);
    }

    $openingCheck = false;   // default

    if ($purchase) {
      // find first Form621 id for this client + item
      $firstId = $this->form621->where('client_id', $purchase->client_id)->where('item_id',   $purchase->item_id)->orderBy('id', 'asc')->value('id');
      // current form621 id
      $currentForm621Id = $this->form621->where('importable_id', $id)->value('id');

      // if you are passing Form621 id in $id, compare with $currentForm621Id
      $openingCheck = ($firstId && (int) $firstId === (int) $currentForm621Id);
    }

    if (!$purchase) {
      return response()->json(['msg' => 'Nothing found']);
    }

    return response()->json(['purchase' => $purchase, 'openingcheck' => $openingCheck]);
  }


  // function call form importer purchase page for update
  public function update(Request $request, $id)
  {
    $user = Auth::user();

    if (!$this->form621->where('importable_id', $id)->where('importable_type', 'purchase')->where('last', 1)->first()) {
      return back()->with('error', 'Can not update: not last combination');
    }

    // calculation part for validation and insert data
    $av      = (float) $request->input('assessable_value');
    $cd      = (float) $request->input('cd', 0);
    $rd      = (float) $request->input('rd', 0);
    $sd      = (float) $request->input('sd', 0);
    $vat     = (float) $request->input('vat', 0);
    $at      = (float) $request->input('at', 0);
    $mcu     = (float) $request->input('mcu', 0);
    $qty     = (float) $request->input('quantity');
    $exQty   = (float) $request->input('excess_quantity', 0);
    $wastage = (float) $request->input('wastage', 0);

    $cdValue   = $av * ($cd / 100);
    $rdValue   = $av * ($rd / 100);
    $sdValue   = ($av + $cdValue + $rdValue) * ($sd / 100);
    $baseValue = $av + $cdValue + $rdValue + $sdValue;
    $vatValue  = $baseValue * ($vat / 100);
    $atValue   = $baseValue * ($at / 100);
    $totalQuantity = ($qty + $exQty) - (($qty + $exQty) * ($wastage / 100));

    // NEW: fetch Form 4.3 to get factor BEFORE validation (to compute converted net qty)
    $form43ForFactor = $this->form43->where([
      ['client_id', $request->input('client_id')],
      ['item_id',   $request->input('item_id')],
      ['last', 1]
    ])->first();
    $factor = (float) ($form43ForFactor->factor ?? 1);

    // NEW: converted quantities in sale unit (for validation + insert)
    $qtyConv            = (float) $qty   * $factor;
    $exQtyConv          = (float) $exQty * $factor;
    $totalQuantityConv  = (float) $totalQuantity * $factor;
    $unitPriceConv      = ($totalQuantityConv > 0) ? ($baseValue / $totalQuantityConv) : null;
    $mcuAmount          = (float) $av * $mcu;

    $formField = $request->validate(
      [
        'client_id' => ['required', 'numeric', 'gt:0', function ($attribute, $value, $fail) use ($user) {
          $client = $this->client->find($value);

          if (!$client) return $fail('Client does not exist');

          if ($client->status == 0) return $fail('BIN inactive');

          if (!$this->hasRole(['admin']) && $client->user_id !== $user->id) return $fail('Unauthorised');
        }],
        'item_id' => ['required', 'integer', 'gt:0', 'bail', Rule::exists('items', 'id'), function ($attribute, $value, $fail) use ($request) {
          $item = $this->item->find($value);
          if (!$item) return $fail('Item not exists');

          $clientId = $request->input('client_id');

          $form43 = $this->form43->where('client_id', $clientId)->where('item_id', $value)->where('last', 1)->first();

          if (!$form43) return $fail('4.3 not found');

          if (!$form43->additions()->exists()) return $fail('4.3 has no addition');
        }],
        'lc_no' => ['required', 'max:50', function ($attribute, $value, $fail) use ($request) {
          $purchase = $this->purchase->where('lc_no', $value)->first();

          if ($purchase && ($purchase->client_id != $request->client_id)) {
            return $fail("Not same client");
          }
        }],
        'lc_date' => ['nullable', 'date_format:"Y-m-d"', function ($attribute, $value, $fail) {
          $entrydate = Carbon::parse($value, 'Asia/Dhaka')->startOfDay();
          $today   = Carbon::today('Asia/Dhaka')->startOfDay();

          // Stop if entry date is over current date (future date)
          if ($entrydate->gt($today)) {
            return $fail("Greater than today");
          }
        }],
        'boe' => 'required|max:10',
        'boe_date' => ['required', 'date_format:"Y-m-d"', function ($attribute, $value, $fail) use ($request) {
          $entrydate = Carbon::parse($request->input('entry_date'), "Asia/Dhaka")->startOfDay();
          $boedate = Carbon::parse($value, 'Asia/Dhaka')->startOfDay();
          $today   = Carbon::today('Asia/Dhaka')->startOfDay();

          // Stop if entry date is over current date (future date)
          if ($boedate->gt($today)) {
            return $fail("Greater than today");
          }

          // error show if boe date is big from entry date
          if ($boedate->gt($entrydate)) {
            return $fail("BoE date > Entry date");
          }
        }],
        'entry_date' => ['required', 'bail', 'date_format:"Y-m-d"', 'bail', function ($attribute, $value, $fail) use ($request) {
          $entrydate = Carbon::parse($value, 'Asia/Dhaka')->startOfDay();
          $today   = Carbon::today('Asia/Dhaka')->startOfDay();

          // Stop if entry date is over current date (future date)
          if ($entrydate->gt($today)) {
            return $fail("Greater than today");
          }

          $lastDate = $this->form621->where('client_id', $request->input('client_id'))->groupBy(['client_id'])->max('date');
          if ($lastDate && $lastDate > $value) {
            return $fail("Violate last date");
          }
        }],
        'mushaknote_id' => ['required', 'numeric', 'gt:0', 'bail', function ($attribute, $value, $fail) use ($request) {
          // find note id from mushaknote
          $note15 = $this->mushaknote->where('name_note_no', 'note_15')->value('id');
          $gap = $this->vatfactor->where('name', 'month_gap')->value('value');

          // Only run gap + receipt checks when note_15 is selected
          if ($note15 && $note15 == $value) {

            // Only run date gap check when both dates are present
            if ($request->boe_date && $request->entry_date) {

              $boe   = Carbon::parse($request->input('boe_date'), 'Asia/Dhaka');
              $entry = Carbon::parse($request->input('entry_date'), 'Asia/Dhaka');

              $boeIndex   = $boe->year * 12 + $boe->month;
              $entryIndex = $entry->year * 12 + $entry->month;
              $monthGap   = ($entryIndex - $boeIndex) + 1;

              if ($monthGap > $gap) return $fail('Over ' . $gap . ' tax period');
            }
          }

          if ($note15 && ($note15 == $value) && (empty($request->input('receipt_number')) || empty($request->input('receipt_date')))) {
            return $fail('Need receipt number and date');
          }

          // check exists or not
          if (!$this->mushaknote->where('id', $value)->exists()) return $fail('Not exists');
        }],
        'stationcode_id' => ['required', 'numeric', 'gt:0', function ($attribute, $value, $fail) use ($request) {
          if (!$this->stationcode->where('id', $value)->exists()) {
            return $fail('Not exists');
          }
        }],
        'df_cvat_cf' => 'required|numeric|gte:0',
        'assessable_value' => 'required|numeric|gt:0',
        'quantity' => 'required|numeric|gt:0',
        'excess_quantity' => 'required|numeric|gte:0',
        'wastage' => 'required|numeric|gte:0|decimal:0,2|between:0,100.00',
        'unit_id' => ['required', 'numeric', 'gt:0', function ($attribute, $value, $fail) use ($request) {
          // last form43 id find out with client_id, item_id
          $unitId = $this->form43->where([['client_id', $request->input('client_id')], ['item_id', $request->input('item_id')], ['last', 1]])->value('sale_unit_id');

          // error show if not match form43
          if (empty($unitId) || (int)$unitId !== (int)$value) {
            return $fail("Not match");
          }

          // check exists or not
          if (!$this->unit->where('id', $value)->exists()) {
            return $fail('Not exists');
          }
        }],
        'base_value' => 'nullable|numeric|gt:0',
        // NEW: validate against converted unit price (based on converted net qty)
        'unit_price'  => ['nullable', 'numeric', 'gt:0', 'bail', function ($attribute, $value, $fail) use ($request, $unitPriceConv, $form43ForFactor) {
          if ($unitPriceConv === null || $unitPriceConv <= 0) {
            return $fail('Computed unit price invalid');
          }

          $form43UnitPrice = $form43ForFactor?->unit_price ?? $this->form43->where([['client_id', $request->input('client_id')], ['item_id', $request->input('item_id')], ['last', 1]])->value('unit_price');

          $calc = !empty($form43UnitPrice) ? ((($unitPriceConv - $form43UnitPrice) / $form43UnitPrice) * 100) : null;

          $vatfactor = $this->vatfactor->where('name', 'vatfact')->value('value');

          if (!empty($form43UnitPrice) && $vatfactor !== null) {
            if ($calc >  (float)$vatfactor) return $fail('Over ' . $vatfactor . ' (' . number_format($calc, 2, '.', '') . '%)');
            if ($calc < -(float)$vatfactor) return $fail('Less ' . $vatfactor . ' (' . number_format($calc, 2, '.', '') . '%)');
          }
        }],
        'cd' => 'required|numeric|gte:0',
        'rd' => 'required|numeric|gte:0',
        'sd' => 'required|numeric|gte:0',
        'vat' => ['required', 'numeric', 'gte:0', 'between:0,15.00', function ($attribute, $value, $fail) use ($request) {
          // get note_15 id
          $note15 = $this->mushaknote->where('name_note_no', 'note_15')->value('id');

          // If mushaknote_id = note_15 → VAT MUST be 15
          if ($request->mushaknote_id == $note15 && $value != 15) {
            return $fail('Set 15%');
          }
        }],
        'at' => ['required', 'numeric', 'gte:0', function ($attribute, $value, $fail) use ($request) {
          // Skip checks until BOTH dates are filled
          if (!$request->boe_date || !$request->entry_date) return;

          $boe   = Carbon::parse($request->boe_date, 'Asia/Dhaka');
          $entry = Carbon::parse($request->entry_date, 'Asia/Dhaka');

          $boeIndex   = $boe->year * 12 + $boe->month;
          $entryIndex = $entry->year * 12 + $entry->month;
          $monthGap   = ($entryIndex - $boeIndex) + 1;

          $gap = $this->vatfactor->where('name', 'month_gap')->value('value');

          // Over X months → AT must be 0
          if ($monthGap > $gap && $value > 0) return $fail("Set 0%, Over " . $gap . " months");

          // max AT check
          $vatfactor = $this->vatfactor->where('name', 'atfact')->value('value');

          if ($value > $vatfactor) return $fail('Range 0 to (' . number_format($vatfactor, 2, '.', '') . '%)');
        }],
        'opening_stock' => [Rule::requiredIf(function () use ($request) {
          return !$this->form621->where([['client_id', $request->input('client_id')], ['item_id',  $request->input('item_id')]])
            ->exists();
        }), 'numeric', 'gte:0'],
        'mcu' => 'required|numeric|gte:0',
        'mcu_amount' => 'nullable|numeric|gte:0',
        'receipt_number' => 'nullable|max:20',
        'receipt_date' => ['nullable', 'date_format:"Y-m-d"', function ($attribute, $value, $fail) {
          $date = Carbon::parse($value, 'Asia/Dhaka')->startOfDay();
          $today = Carbon::today('Asia/Dhaka')->startOfDay();

          // Stop if entry date is over current date (future date)
          if ($date->gt($today)) {
            return $fail("Greater than today");
          }
        }],
        'exporter_name' => 'nullable|max:100',
        'address' => 'nullable|max:100',
      ],
      [
        'client_id.required' => 'BIN required',
        'client_id.numeric' => 'Number only',
        'client_id.gt' => 'Number >0',
        'item_id.required' =>  'Item required',
        'item_id.numeric' =>  'Number only',
        'item_id.gt' =>  'Number > 0',
        'lc_no.required' => 'LC number required',
        'lc_no.max' => 'Max 50 char',
        'lc_date.date_format' =>  'Invalid date',
        'boe.required' =>  'BoE required',
        'boe.max' =>  'Max 10 char',
        'boe_date.required' =>  'BoE date required',
        'boe_date.date_format' =>  'Invalid date',
        'entry_date.required' =>  'Entry date required',
        'entry_date.date_format' =>  'Invalid date',
        'mushaknote_id.required' =>  'Mushak note required',
        'mushaknote_id.numeric' =>  'Number only',
        'mushaknote_id.gt' =>  'Number > 0',
        'stationcode_id.required' => 'Station code required',
        'stationcode_id.numeric' => 'Number only',
        'stationcode_id.gt' => 'Number > 0',
        'df_cvat_cf.required' =>  'Df/CVAT/VF required',
        'df_cvat_cf.numeric' =>  'Number only',
        'df_cvat_cf.gte' =>  '(+)Number only',
        'assessable_value.required' =>  'Assessable value required',
        'assessable_value.numeric' =>  'Number only',
        'assessable_value.gt' =>  'Number > 0',
        'quantity.required' =>  'Quantity required',
        'quantity.numeric' =>  'Number only',
        'quantity.gt' =>  'Number > 0',
        'excess_quantity.required' =>  'Excess quantity required',
        'excess_quantity.numeric' =>  'Number only',
        'excess_quantity.gt' =>  'Number > 0',
        'wastage.required' =>  'Wastage required',
        'wastage.numeric' =>  'Number only',
        'wastage.gte' =>  '(+)Number only',
        'wastage.between' =>  '0 to 100.00',
        'unit_id.required' =>  'Unit required',
        'unit_id.numeric' =>  'Number only',
        'unit_id.gt' =>  'Number > 0',
        'base_value.numeric' =>  'Number only',
        'base_value.gt' =>  'Number > 0',
        'unit_price.numeric' =>  'Number only',
        'unit_price.gt' =>  'Number > 0',
        'net_quantity.required' =>  'Net quantity required',
        'net_quantity.numeric' =>  'Number only',
        'net_quantity.gt' =>  '(+)Number only',
        'cd.required' =>  'CD required',
        'cd.numeric' =>  'Number only',
        'cd.gte' =>  '(+)Number only',
        'rd.required' =>  'RD required',
        'rd.numeric' =>  'Number only',
        'rd.gte' =>  '(+)Number only',
        'sd.required' =>  'SD required',
        'sd.numeric' =>  'Number only',
        'sd.gte' =>  '(+)Number only',
        'vat.required' =>  'VAT required',
        'vat.numeric' =>  'Number only',
        'vat.gte' =>  '(+)Number only',
        'vat.between' =>  'Range 0 to 15',
        'at.required' =>  'AT required',
        'at.numeric' =>  'Number only',
        'at.gte' =>  '(+)Number only',
        'at.between' =>  'Range 0 to 7.50',
        'opening_stock.required' => 'Opening stock required',
        'opening_stock.numeric' =>  'Number only',
        'opening_stock.gte' =>  '(+)Number only',
        'mcu.required' =>  'MCU required',
        'mcu.numeric' =>  'Number only',
        'mcu.gte' =>  '(+)Number only',
        'mcu_amount.numeric' =>  'Number only',
        'mcu_amount.gte' =>  '(+)Number only',
        'receipt_number.max' => 'Max 20 char',
        'receipt_date.date_format' => 'Invalid date',
        'exporter_name' => 'Max 100 char',
        'address.max' => 'Max 100 char',
      ]
    );

    // use transaction for those exception
    try {
      DB::transaction(function () use ($id, $formField, $request, $user, $cdValue, $rdValue, $sdValue, $baseValue, $vatValue, $atValue, $form43ForFactor, $qtyConv, $exQtyConv, $totalQuantityConv, $unitPriceConv, $mcuAmount) {
        // NEW: set purchase unit_id to sale unit (since quantities are converted)
        $form43 = $form43ForFactor ?: $this->form43->where([['client_id', $request->input('client_id')], ['item_id',   $request->input('item_id')], ['last', 1]])->first();

        if (!$form43) {
          throw new \RuntimeException('Form 4.3 not found for conversion.');
        }

        $openingInput = (float) $request->input('opening_stock', 0);
        $quantity = (float) $totalQuantityConv;
        $stock    = $openingInput + $quantity;

        $formField['cd_value']        = number_format($cdValue, 2, ".", "");
        $formField['rd_value']        = number_format($rdValue, 2, ".", "");
        $formField['sd_value']        = number_format($sdValue, 2, ".", "");
        $formField['vat_value']       = number_format($vatValue, 2, ".", "");
        $formField['at_value']        = number_format($atValue, 2, ".", "");
        $formField['base_value']      = number_format($baseValue, 2, ".", "");

        // NEW: override purchase with CONVERTED (sale unit) values
        $formField['quantity']        = number_format($qtyConv,           2, ".", "");
        $formField['excess_quantity'] = number_format($exQtyConv,         2, ".", "");
        $formField['total_quantity']  = number_format($totalQuantityConv, 2, ".", "");
        $formField['unit_price']      = number_format($unitPriceConv,     2, ".", "");
        $formField['mcu_amount']      = number_format($mcuAmount,         2, ".", "");
        $formField['unit_id']         = $form43->sale_unit_id;
        $formField['factor']          = $form43->factor;
        $formField['stock']           = number_format($stock, 2, ".", "");

        $purchase = $this->purchase->find($id);

        if (!$purchase) {
          throw new \RuntimeException('Purchase not found.');
        }

        // update the model
        $purchase->update($formField);

        // insert into form621 (converted, sale unit)
        $purchase->form621()->where('importable_id', $id)->where('importable_type', 'purchase')->update([
          'client_id'        => $formField['client_id'],
          'item_id'          => $formField['item_id'],
          'form43_id'        => $form43->id,
          'lc_or_invoice_no' => $formField['lc_no'],
          'boe'              => $formField['boe'],
          'quantity'         => number_format($quantity, 2, ".", ""),                 // converted
          'unit_id'          => $form43->sale_unit_id,     // sale unit
          'opening_stock'    => number_format($openingInput, 2, ".", ""),
          'stock'            => number_format($stock, 2, ".", ""),                    // converted
          'date'             => $formField['entry_date'],
          'userid'           => $user->id,
        ]);
      });

      // realtime data
      event(new CommonEvent($user->id));

      return back()->with("message", 'Purchase updated');
    } catch (Throwable $e) {
      return back()->with("error", $e->getMessage());
    }
  }

  // function call from purchase page
  public function updateExporter(Request $request, $id)
  {
    $user = Auth::user();

    $purchase = $this->purchase->with(['client'])->where('id', $id)->first();

    if (!$this->hasRole(['admin']) && ($purchase->client->user_id !== $user->id)) {
      return back()->with('error', 'Unauthorized');
    }

    $formField = $request->validate(
      [
        'mushaknote_id' => ['required', 'numeric', 'gt:0', 'bail', function ($attribute, $value, $fail) use ($request) {
          // find note id from mushaknote
          $note15 = $this->mushaknote->where('name_note_no', 'note_15')->value('id');

          if ($note15 && ($note15 == $value) && (empty($request->input('receipt_number')) || empty($request->input('receipt_date')))) {
            return $fail('Need receipt number and date');
          }

          // check exists or not
          if (!$this->mushaknote->where('id', $value)->exists()) return $fail('Not exists');
        }],
        'receipt_number' => 'nullable|max:20',
        'receipt_date' => ['nullable', 'date_format:"Y-m-d"', function ($attribute, $value, $fail) {
          $date = Carbon::parse($value, 'Asia/Dhaka')->startOfDay();
          $today = Carbon::today('Asia/Dhaka')->startOfDay();

          // Stop if entry date is over current date (future date)
          if ($date->gt($today)) {
            return $fail("Greater than today");
          }
        }],
        'exporter_name' => 'nullable|max:100',
        'address' => 'nullable|max:100'
      ],
      [
        'mushaknote_id.required' =>  'Mushak note required',
        'mushaknote_id.numeric' =>  'Number only',
        'mushaknote_id.gt' =>  'Number > 0',
        'receipt_number.max' => 'Max 20 char',
        'receipt_date.date_format' => 'Invalid date',
        'exporter_name.max' => 'Max 100 char',
        'address.max' => 'Max 100 char'
      ]
    );

    $this->purchase->where('id', $id)->update($formField);

    return back()->with('message', 'Exporter updated');
  }

  // function call from importer purchase page for delete
  public function destroy($id)
  {
    $user = Auth::user();

    try {
      DB::transaction(function () use ($id, $user) {
        $permission = $this->form621->with(['client'])->where('id', $id)
          ->when(!$this->hasRole(['admin']), fn($q) => $q->whereHas('client', fn($c) => $c->where('user_id', $user->id)))
          ->first();

        if (!$permission) {
          return back()->with('error', 'Unauthorized');
        }

        $form621 = $this->form621->with(['client'])->where([['id', $id], ['importable_type', 'purchase'], ['last', 1]]);

        if (!$form621->first()) {
          return back()->with('error', 'Can not delete: not last combination');
        }

        // temporary store data in variable
        $storeData = $form621->first();
        // delete current id
        $this->purchase->where('id', $storeData->importable_id)->delete();
        $form621->delete();

        // set the latest remaining record as last=1 (if any)
        $newLastId = $this->form621->where('client_id', $storeData->client_id)->where('item_id', $storeData->item_id)->max('id');
        if ($newLastId) {
          $this->form621->where('id', $newLastId)->update(['last' => 1]);
        }
      });

      // realtime data
      event(new CommonEvent($user->id));

      return back()->with('message', 'Purchase deleted');
    } catch (Throwable $e) {
      return back()->with("error", $e->getMessage());
    }
  }

  /**
   * Here lc search from purchase for
   * when type lc or copy paste lc into
   * field that time forntend call
   * this api to get lc detail
   */
  public function purchaseLcSearch(Request $request)
  {
    $user = Auth::user();

    $lc = $request->input('lc_no');
    $client = $request->input('client_id');
    $item = $request->input('item_id');

    $lcSearch = $this->purchase->with(['stationcode'])
      ->when(!$this->hasRole(['admin']), fn($query) => $query->whereHas('client', fn($q) => $q->where('user_id', $user->id)))
      ->select('id', 'lc_date', 'boe_date', 'entry_date', 'stationcode_id')
      ->where('client_id', $client)
      ->where('item_id', $item)
      ->where('lc_no', $lc)
      ->first();
    return response()->json(['lcsearch' => $lcSearch]);
  }
}
