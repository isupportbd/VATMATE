<?php

namespace Modules\Sale\Http\Controllers;

use App\Events\CommonEvent;
use App\Http\Controllers\Controller;
use App\Traits\ChecksUserRole;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Modules\Bank\Models\Bank;
use Modules\Client\Models\Client;
use Modules\CustomerManagement\Models\CustomerManagement;
use Modules\Form43\Models\Form43;
use Modules\Form621\Models\Form621;
use Modules\Item\Models\Item;
use Modules\Mushaknote\Models\Mushaknote;
use Modules\Purchase\Models\Purchase;
use Modules\Rate\Models\Rate;
use Modules\Sale\Models\Sale;
use Modules\Unit\Models\Unit;
use Modules\Vatfactor\Models\Vatfactor;
use Throwable;

class SaleController extends Controller
{
  use ChecksUserRole;

  public $client;
  public $sale;
  public $purchase;
  public $form43;
  public $form621;
  public $item;
  public $unit;
  public $vatfactor;
  public $bank;
  public $customer;
  public $mushaknote;
  public $rate;
  public function __construct(Client $client, Sale $sale, Purchase $purchase, Form43 $form43, Form621 $form621, Item $item, Unit $unit, Vatfactor $vatfactor, Bank $bank, CustomerManagement $customer, Mushaknote $mushaknote, Rate $rate)
  {
    $this->sale = $sale;
    $this->purchase = $purchase;
    $this->form43 = $form43;
    $this->form621 = $form621;
    $this->client = $client;
    $this->item = $item;
    $this->unit = $unit;
    $this->vatfactor = $vatfactor;
    $this->bank = $bank;
    $this->customer = $customer;
    $this->mushaknote = $mushaknote;
    $this->rate = $rate;
  }

  public function index(Request $request)
  {
    $user = Auth::user();
    $search = trim((string) $request->input('search', ''));
    $termLike   = $search !== '' ? "%$search%" : null;

    $sale = $this->sale->with(['client.division', 'client.user', 'client.user.role', 'item', 'unit', 'bank'])
      ->when(!$this->hasRole(['admin']), fn($query) => $query->whereHas('client', fn($q) => $q->where('user_id', $user->id)))
      ->when($search !== '', function ($query) use ($termLike) {
        $query->where(function ($query) use ($termLike) {
          $query->whereHas('client', function ($query) use ($termLike) {
            $query->where('bin', 'ilike', $termLike)
              ->orWhere('company_name', 'ilike', $termLike)
              ->orWhere('mobile', 'ilike', $termLike);
          })->orWhereHas('item', fn($query) => $query->where('name', 'ilike', $termLike));
        });
      });

    $result = $sale->orderBy('id', 'desc')->paginate((int)request('size', 10))->withQueryString();

    return Inertia("sale/index", ['sale' => $result, 'search' => $search]);
  }

  /**
   * Store a newly created resource in storage.
   */
  public function store(Request $request)
  {
    $user    = Auth::user();

    $items = collect($request->input('items', []));

    if ($items->isEmpty()) {
      return back()->with('error', 'No items provided');
    }

    // ─────────────────────────────────────────────
    // 1. HEADER DATA (one per invoice)
    // ─────────────────────────────────────────────
    $clientId        = $request->input('client_id');
    $date            = $request->input('date');
    $customerMobile  = $request->input('customer_mobile');
    $customerName    = $request->input('customer_name');
    $binOrNid        = $request->input('bin_or_nid');
    $address         = $request->input('address');
    $bankId          = $request->input('bank_id');
    $transactionNo   = $request->input('transaction_no');
    $transactionDate = $request->input('transaction_date');

    // VAT 15% from mushaknote (note_15)
    $note15 = (float) $this->mushaknote
      ->where('name_note_no', 'note_15')
      ->value('percent');

    // ─────────────────────────────────────────────
    // 2. GRAND TOTAL (ONLY ITEMS WITH VAT 15)
    //    check BOTH mushaknote + rate table
    // ─────────────────────────────────────────────
    $grandTotal = $items->reduce(function ($carry, $row) use ($note15, $clientId) {
      $itemId = $row['item_id'] ?? null;
      if (!$itemId) {
        return $carry;
      }

      $vatRow = (float) ($row['vat'] ?? 0);

      // per-item VAT from rate table
      $tare = (float) $this->rate
        ->where('client_id', $clientId)
        ->where('item_id', $itemId)
        ->where('last', 1)
        ->value('vat');

      // item is "VAT 15" only if either matches note15
      $isVat15 = ($vatRow == $note15) || ($tare == $note15);

      if (!$isVat15) {
        return $carry;
      }

      $qty = (float) ($row['quantity'] ?? 0);
      $up  = (float) ($row['unit_price'] ?? 0);
      $sd  = (float) ($row['sd'] ?? 0);

      $itemPrice  = $qty * $up;
      $sdValue    = $itemPrice * ($sd / 100);
      $vatValue   = ($itemPrice + $sdValue) * ($vatRow / 100);
      $totalPrice = $itemPrice + $sdValue + $vatValue;

      return $carry + $totalPrice;
    }, 0.0);

    // ─────────────────────────────────────────────
    // 3. VALIDATION
    // ─────────────────────────────────────────────
    $saleLimit       = $this->vatfactor->where('name', 'sale_limit')->value('value');
    $needPaymentInfo = $grandTotal > $saleLimit;

    $formField = $request->validate(
      [
        'client_id' => ['required', 'numeric', 'gt:0', function ($attribute, $value, $fail) use ($user) {
          $client = $this->client->find($value);

          if (!$client) return $fail('Client does not exist');
          if ($client && $client->status == 0) return $fail('BIN inactive');
          if (!$this->hasRole(['admin']) && $client && $client->user_id !== $user->id) return $fail('Unauthorised');
        }],
        'date' => ['required', 'date_format:"Y-m-d"', function ($attribute, $value, $fail) use ($request) {
          $entrydate = Carbon::parse($value, 'Asia/Dhaka')->startOfDay();
          $today     = Carbon::today('Asia/Dhaka')->startOfDay();

          if ($entrydate->gt($today)) return $fail("Greater than today");

          $lastDate = $this->form621
            ->where('client_id', $request->input('client_id'))
            ->groupBy(['client_id'])
            ->max('date');

          if ($lastDate && ($lastDate > $value)) return $fail("Violate last date");
        }],
        'customer_name'    => 'nullable|max:100',
        'bin_or_nid'       => 'nullable|max:17',
        'address'          => 'nullable|max:150',
        'bank_id' => [$needPaymentInfo ? 'required' : 'nullable', 'numeric', 'gt:0', function ($attribute, $value, $fail) {
          if ($value) {
            $bank = $this->bank->find($value);
            if (!$bank) return $fail('Not exists');
          }
        }],
        'transaction_no'   => [$needPaymentInfo ? 'required' : 'nullable', 'max:50'],
        'transaction_date' => [$needPaymentInfo ? 'required' : 'nullable', 'date_format:Y-m-d'],
        'items' => ['required', 'array', 'min:1', function ($attribute, $value, $fail) {
          $itemIds = collect($value)->pluck('item_id');

          if ($itemIds->count() !== $itemIds->unique()->count()) {
            return $fail("Duplicate items found");
          }
        }],
        'items.*.item_id' => ['required', 'numeric', 'gt:0', function ($attribute, $value, $fail) use ($request, $note15) {
          $clientId = $request->input('client_id');
          if (!$clientId || !$value) return;

          // 1. LATEST PURCHASE (form621) check
          $purchaseRow = $this->form621
            ->where('client_id', $clientId)
            ->where('item_id', $value)
            ->where('importable_type', 'purchase')
            ->orderByDesc('id')
            ->first();

          if (!$purchaseRow || !$purchaseRow->form43_id) {
            return $fail('6.2.1 has no 4.3 reference');
          }

          // 2. 4.3 addition check
          $form43 = $this->form43->with('additions')->find($purchaseRow->form43_id);

          if (!$form43) {
            return $fail('4.3 not found');
          }

          if (!$form43->additions()->exists()) {
            return $fail('4.3 has no addition');
          }

          // 3. RATE table check (MISSING RATE = ERROR)
          $tare = $this->rate
            ->where('client_id', $clientId)
            ->where('item_id', $value)
            ->where('last', 1)
            ->value('vat');

          if ($tare === null) {
            return $fail("Rate not found");
          }
        }],
        'items.*.quantity' => ['required', 'numeric', 'gte:0', function ($attribute, $value, $fail) use ($request) {
          $clientId = $request->input('client_id');
          if (!$clientId) return;

          if (!preg_match('/items\.(\d+)\.quantity/', $attribute, $m)) return;

          $index  = (int) $m[1];
          $itemId = data_get($request->input('items'), "$index.item_id");

          if (!$itemId) return;

          $form621Last = $this->form621
            ->where('client_id', $clientId)
            ->where('item_id', $itemId)
            ->where('last', 1)
            ->first();

          if (!$form621Last) return $fail('Stock not found');
          if ($form621Last && ((float) $value > (float) $form621Last->stock)) return $fail("Over the stock");
        }],
        'items.*.unit_id'    => 'required|numeric|gt:0',
        'items.*.unit_price' => 'required|numeric|gt:0',
        'items.*.sd'         => 'required|numeric|gte:0',
        'items.*.vat'        => 'required|numeric|gte:0|between:0,15.00',
      ],
      [
        'client_id.required'               => 'BIN required',
        'client_id.numeric'                => 'Number only',
        'client_id.gt'                     => 'Number > 0',
        'date.required'                    => 'Date required',
        'date.date_format'                 => 'Invalid date',
        'customer_mobile.max'              => 'Max 11 char',
        'customer_name.max'                => 'Max 100 char',
        'bin_or_nid.max'                   => 'Max 17 char',
        'address.max'                      => 'Max 150 char',
        'bank_id.required'                 => 'Bank required',
        'bank_id.numeric'                  => 'Number only',
        'bank_id.gt'                       => 'Number > 0',
        'transaction_no.required'          => 'Transaction number required',
        'transaction_no.max'               => 'Max 50 char',
        'transaction_date.required'        => 'Transaction date required',
        'transaction_date.date_format'     => 'Invalid date',
        'items.required'                   => 'At least one item required',
        'items.array'                      => 'Invalid items data',
        'items.*.item_id.required'         => 'Item required',
        'items.*.item_id.numeric'          => 'Number only',
        'items.*.item_id.gt'               => 'Number > 0',
        'items.*.unit_id.required'         => 'Unit required',
        'items.*.unit_id.numeric'          => 'Number only',
        'items.*.unit_id.gt'               => 'Number > 0',
        'items.*.quantity.required'        => 'Quantity required',
        'items.*.quantity.numeric'         => 'Number only',
        'items.*.quantity.gte'             => '(+)Number only',
        'items.*.unit_price.required'      => 'Unit price required',
        'items.*.unit_price.numeric'       => 'Number only',
        'items.*.unit_price.gt'            => 'Number > 0',
        'items.*.sd.required'              => 'SD required',
        'items.*.sd.numeric'               => 'Number only',
        'items.*.sd.gte'                   => '(+)Number only',
        'items.*.vat.required'             => 'VAT required',
        'items.*.vat.numeric'              => 'Number only',
        'items.*.vat.gte'                  => '(+)Number only',
        'items.*.vat.between'              => 'Range 0 to 15',
      ]
    );

    try {
      DB::transaction(function () use ($user, $clientId, $items, $date, $customerMobile, $customerName, $binOrNid, $address, $bankId, $transactionNo, $transactionDate, $note15) {
        $currentYear = now('Asia/Dhaka')->format('Y');

        // LAST INVOICE FOR THIS BIN + THIS YEAR
        $lastInvoice = $this->sale->query()
          ->when(!$this->hasRole(['admin']), fn($q) => $q->whereHas('client', fn($q) => $q->where('user_id', $user->id)))
          ->where('client_id', $clientId)
          ->whereYear('date', $currentYear)
          ->orderByDesc('id')
          ->first();

        if ($lastInvoice) {
          $parts = explode('/', $lastInvoice->invoice_no);
          $number = isset($parts[1]) ? (int) $parts[1] : 0;
          $nextInvoiceNumber = str_pad($number + 1, 5, '0', STR_PAD_LEFT);
          $invoice = 'INV-' . now('Asia/Dhaka')->format('y') . '/' . $nextInvoiceNumber;
        } else {
          $invoice = 'INV-' . now('Asia/Dhaka')->format('y') . '/00001';
        }

        // LOOP THROUGH ITEMS
        foreach ($items as $row) {
          $itemId = $row['item_id'];
          $unitId = $row['unit_id'];
          $qty    = (float) $row['quantity'];
          $up     = (float) $row['unit_price'];
          $sd     = (float) $row['sd'];
          $vat    = (float) $row['vat'];

          // per-item VAT from rate table
          $tare = $this->rate
            ->where('client_id', $clientId)
            ->where('item_id', $itemId)
            ->where('last', 1)
            ->value('vat');

          // item is "VAT 15" only if either matches note15
          $isVat15 = ($vat == $note15) || ((float)$tare == $note15);

          // backend calculations (ignore any totals from frontend)
          $itemPrice  = $qty * $up;
          $sdValue    = $itemPrice * ($sd / 100);
          $vatValue   = ($itemPrice + $sdValue) * ($vat / 100);
          $totalPrice = $itemPrice + $sdValue + $vatValue;

          // LAST form_6.2.1 for this client + item
          $lastId = $this->form621
            ->where('client_id', $clientId)
            ->where('item_id', $itemId)
            ->where('last', 1)
            ->max('id');

          $form621Last = $lastId ? $this->form621->where('id', $lastId)->first() : null;

          if (!$form621Last) {
            throw new \RuntimeException("Stock not found for this item");
          }

          // Mark previous last as not last
          $form621Last->update(['last' => 0]);

          // Get latest purchase row (form621) to fetch form43_id
          $purchaseRow = $this->form621
            ->where('client_id', $clientId)
            ->where('item_id', $itemId)
            ->where('importable_type', 'purchase')
            ->orderByDesc('id')
            ->first();

          $purchase = $this->purchase->find($purchaseRow->importable_id);

          if (!$purchase || !$purchase->unit_price) {
            throw new \RuntimeException("No purchase data found for this item");
          }

          $lastPurchaseUnitPrice = $purchase->unit_price;

          $stockAfter = $form621Last->stock - $qty;

          // CREATE SALE ROW
          $sale = $this->sale->create([
            'invoice_no'                => $invoice,
            'client_id'                 => $clientId,
            'date'                      => $date,
            'customer_mobile'           => $customerMobile,
            'customer_name'             => $customerName,
            'bin_or_nid'                => $binOrNid,
            'address'                   => $address,

            // only for items identified as VAT 15
            'bank_id'                   => $isVat15 ? $bankId : null,
            'transaction_no'            => $isVat15 ? $transactionNo : null,
            'transaction_date'          => $isVat15 ? $transactionDate : null,

            'opening_stock'             => number_format($form621Last->stock, 2, ".", ""),
            'stock'                     => number_format($stockAfter, 2, ".", ""),
            'item_id'                   => $itemId,
            'unit_id'                   => $unitId,
            'quantity'                  => number_format($qty, 2, ".", 2),
            'last_purchase_unit_price'  => number_format($lastPurchaseUnitPrice, 2, '.', ''),
            'unit_price'                => number_format($up, 2, '.', ''),
            'item_price'                => number_format($itemPrice, 2, '.', ''),
            'total_price'               => number_format($totalPrice, 2, '.', ''),
            'sd'                        => number_format($sd, 2, '.', ''),
            'sd_value'                  => number_format($sdValue, 2, '.', ''),
            'vat'                       => number_format($vat, 2, '.', ''),
            'vat_value'                 => number_format($vatValue, 2, '.', ''),
          ]);

          // CREATE 6.2.1 (FORM621) ROW
          $sale->form621()->create([
            'client_id'        => $clientId,
            'item_id'          => $itemId,
            'form43_id'        => $purchaseRow->form43_id,
            'lc_or_invoice_no' => $invoice,
            'quantity'         => number_format($qty, 2, ".", ""),
            'unit_id'          => $unitId,
            'opening_stock'    => number_format($form621Last->stock, 2, ".", ""),
            'stock'            => number_format($stockAfter, 2, ".", ""),
            'date'             => $date,
            'last'             => 1,
            'userid'           => $user->id,
          ]);
        }
      });

      // realtime data
      event(new CommonEvent($user->id));

      return back()->with('message', 'Sale created');
    } catch (Throwable $e) {
      return back()->with('error', $e->getMessage());
    }
  }

  /**
   * Show the specified resource.
   */
  public function show($id)
  {
    $user    = Auth::user();

    $sale = $this->sale->with(['client.division', 'client.user.role', 'item', 'unit', 'bank'])->find($id);

    if (!$this->hasRole(['admin']) && $sale && ($sale->client->user_id !== $user->id)) {
      return response()->json(['sale' => null, 'error' => 'Unauthorized']);
    }

    return response()->json(['sale' => $sale]);
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(Request $request, $id)
  {
    $user    = Auth::user();

    if (!$this->form621->where('importable_id', $id)->where('importable_type', 'sale')->where('last', 1)->first()) {
      back()->with('error', 'Can not update: not last combination');
    }

    $sale = $this->sale->with('form621')->find($id);
    if (!$sale) {
      back()->with('error', 'Sale not found');
    }

    $saleForm621 = optional($sale->form621->first());
    if (!$saleForm621) {
      back()->with('error', 'Related Form 6.2.1 row not found');
    }

    // header
    $clientId        = $request->input('client_id');
    $date            = $request->input('date');
    $customerMobile  = $request->input('customer_mobile');
    $customerName    = $request->input('customer_name');
    $binOrNid        = $request->input('bin_or_nid');
    $address         = $request->input('address');
    $bankId          = $request->input('bank_id');
    $transactionNo   = $request->input('transaction_no');
    $transactionDate = $request->input('transaction_date');

    // single item
    $itemId    = $request->input('item_id');
    $unitId    = $request->input('unit_id');
    $quantity  = (float) $request->input('quantity', 0);
    $unitPrice = (float) $request->input('unit_price', 0);
    $sd        = (float) $request->input('sd', 0);
    $vat       = (float) $request->input('vat', 0);

    // backend calc (same as store)
    $itemPrice  = $quantity * $unitPrice;
    $sdValue    = $itemPrice * ($sd / 100);
    $vatValue   = ($itemPrice + $sdValue) * ($vat / 100);
    $totalPrice = $itemPrice + $sdValue + $vatValue;
    $rowTotal   = $totalPrice; // same as item total

    // note_15 percent
    $note15 = (float) $this->mushaknote
      ->where('name_note_no', 'note_15')
      ->value('percent');

    // rate VAT (tare) for this client+item
    $rateVatForLimit = $this->rate
      ->where('client_id', $sale->client_id)   // client is not changeable
      ->where('item_id',   $sale->item_id)     // item is not changeable
      ->where('last', 1)
      ->value('vat');

    $rateVatForLimitF = $rateVatForLimit !== null ? (float) $rateVatForLimit : 0.0;

    // EXACT same Note15 rule as store():
    // item is "VAT 15" only if either entry VAT or rate VAT matches note15
    $isVat15 = ($vat == $note15) || ($rateVatForLimitF == $note15);

    // sale_limit check – only consider this row if it's VAT 15
    $saleLimit       = $this->vatfactor->where('name', 'sale_limit')->value('value');
    $amountForLimit  = $isVat15 ? $rowTotal : 0.0;
    $needPaymentInfo = ($amountForLimit > $saleLimit);

    // ───────────── VALIDATION (unchanged except it uses $needPaymentInfo) ─────────────
    $formField = $request->validate(
      [
        'client_id' => ['required', 'numeric', 'gt:0', function ($attribute, $value, $fail) use ($sale, $user) {
          $client = $this->client->find($value);
          if (!$client) return $fail('Client does not exist');
          if ($client->status == 0) return $fail('BIN inactive');
          if (!$this->hasRole(['admin']) && $client->user_id !== $user->id) return $fail('Unauthorised');
          if ($value != $sale->client_id) return $fail('Not changable');
        }],
        'date' => ['required', 'date_format:"Y-m-d"', function ($attribute, $value, $fail) use ($sale) {
          $entrydate = Carbon::parse($value, 'Asia/Dhaka')->startOfDay();
          $today     = Carbon::today('Asia/Dhaka')->startOfDay();

          if ($entrydate->gt($today)) return $fail("Greater than today");

          $lastDate = $this->form621->where('client_id', $sale->client_id)->groupBy(['client_id'])->max('date');
          if ($lastDate && ($lastDate > $value)) return $fail("Violate last date");
        }],
        'customer_name' => 'nullable|max:100',
        'bin_or_nid'    => 'nullable|max:17',
        'address'       => 'nullable|max:150',
        'bank_id' => [$needPaymentInfo ? 'required' : 'nullable', 'numeric', 'gt:0', function ($attribute, $value, $fail) {
          if ($value) {
            $bank = $this->bank->find($value);
            if (!$bank) return $fail('Bank not exists');
          }
        }],
        'transaction_no'   => [$needPaymentInfo ? 'required' : 'nullable', 'max:50'],
        'transaction_date' => [$needPaymentInfo ? 'required' : 'nullable', 'date_format:"Y-m-d"'],
        'item_id' => ['required', 'numeric', 'gt:0', function ($attribute, $value, $fail) use ($sale) {
          $item = $this->item->find($value);
          if (!$item) return $fail('Item not exists');
          if ($value != $sale->item_id) return $fail('Not changable');

          $lastForm621Id = $this->form621
            ->where('client_id', $sale->client_id)
            ->where('item_id', $value)
            ->where('last', 1)
            ->max('id');

          if (!$lastForm621Id) return $fail('Not last combination');
          if (optional($sale->form621->first())->id != $lastForm621Id) {
            return $fail('Not editable');
          }

          $purchaseRow = $this->form621
            ->where('client_id', $sale->client_id)
            ->where('item_id', $value)
            ->where('importable_type', 'purchase')
            ->orderByDesc('id')
            ->first();

          if (!$purchaseRow || !$purchaseRow->form43_id) return $fail('4.3 not found');

          $form43 = $this->form43->with('additions')->find($purchaseRow->form43_id);
          if (!$form43) return $fail('4.3 not found');
          if ($form43->additions->isEmpty()) return $fail('4.3 has no addition');

          $rateVat = $this->rate
            ->where('client_id', $sale->client_id)
            ->where('item_id', $value)
            ->where('last', 1)
            ->value('vat');

          if ($rateVat === null) {
            return $fail('Rate not found for this item');
          }
        }],
        'unit_id'    => 'required|numeric|gt:0',
        'quantity'   => ['required', 'numeric', 'gte:0', function ($attribute, $value, $fail) use ($sale) {
          $opening = (float) optional($sale->form621->first())->opening_stock;
          if ((float)$value > $opening) return $fail('Over the stock');
        }],
        'unit_price' => 'required|numeric|gt:0',
        'sd'         => 'required|numeric|gte:0',
        'vat'        => 'required|numeric|gte:0|between:0,15.00',
      ],
      [/* your messages same as before */]
    );

    try {
      DB::transaction(function () use ($user, $sale, $saleForm621, $date, $customerMobile, $customerName, $binOrNid, $address, $bankId, $transactionNo, $transactionDate, $unitId, $quantity, $unitPrice, $sd, $vat, $itemPrice, $totalPrice, $sdValue, $vatValue, $note15, $isVat15) {

        $stockAfter = ((float)$saleForm621->opening_stock) - (float)$quantity;

        $sale->update([
          'date'            => $date,
          'customer_mobile' => $customerMobile,
          'customer_name'   => $customerName,
          'bin_or_nid'      => $binOrNid,
          'address'         => $address,

          // only for items identified as VAT 15
          'bank_id'         => $isVat15 ? $bankId : null,
          'transaction_no'  => $isVat15 ? $transactionNo : null,
          'transaction_date' => $isVat15 ? $transactionDate : null,

          'opening_stock'   => number_format($saleForm621->opening_stock, 2, ".", ""),
          'stock'           => number_format($stockAfter, 2, ".", ""),
          'unit_id'         => $unitId,
          'quantity'        => number_format($quantity, 2, '.', ''),
          'unit_price'      => number_format($unitPrice, 2, '.', ''),
          'item_price'      => number_format($itemPrice, 2, '.', ''),
          'total_price'     => number_format($totalPrice, 2, '.', ''),
          'sd'              => number_format($sd, 2, '.', ''),
          'sd_value'        => number_format($sdValue, 2, '.', ''),
          'vat'             => number_format($vat, 2, '.', ''),
          'vat_value'       => number_format($vatValue, 2, '.', ''),
        ]);

        $saleForm621->update([
          'quantity'      => number_format($quantity, 2, '.', ''),
          'unit_id'       => $unitId,
          'opening_stock' => $saleForm621->opening_stock,
          'stock'         => $stockAfter,
          'date'          => $date,
          'userid'        => $user->id,
        ]);
      });

      // realtime data
      event(new CommonEvent($user->id));

      return back()->with('message', 'Sale updated');
    } catch (Throwable $e) {
      return back()->with('error', $e->getMessage());
    }
  }

  /**
   * Remove the specified resource from storage.
   */
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

        $form621 = $this->form621->with(['client'])->where([['id', $id], ['importable_type', 'sale'], ['last', 1]]);

        if (!$form621->first()) {
          return back()->with('error', 'Can not delete: not last combination');
        }

        // temporary store data in variable
        $storeData = $form621->first();
        // delete current id
        $this->sale->where('id', $storeData->importable_id)->delete();
        $form621->delete();

        // set the latest remaining record as last=1 (if any)
        $newLastId = $this->form621->where('client_id', $storeData->client_id)->where('item_id', $storeData->item_id)->max('id');
        if ($newLastId) {
          $this->form621->where('id', $newLastId)->update(['last' => 1]);
        }
      });

      // realtime data
      event(new CommonEvent($user->id));

      return back()->with('message', 'Sale deleted');
    } catch (Throwable $e) {
      return back()->with("error", $e->getMessage());
    }
  }

  // function call from purchase page
  public function updateCustomer(Request $request, $id)
  {
    $user = Auth::user();

    $sale = $this->sale->with(['client'])->where('id', $id)->first();

    if (!$this->hasRole(['admin']) && ($sale->client->user_id !== $user->id)) {
      return back()->with('error', 'Unauthorized');
    }

    $formField = $request->validate(
      [
        'customer_mobile' => 'nullable|max:11',
        'customer_name' => 'nullable|max:100',
        'bin_or_nid' => 'nullable|max:17',
        'address' => 'nullable|max:150'
      ],
      [
        'customer_mobile.max' => 'Max 11 char',
        'customer_name.max' => 'Max 100 char',
        'bin_or_nid.max' => 'Max 17 char',
        'address.max' => 'Max 100 char'
      ]
    );

    $this->sale->where('id', $id)->update($formField);

    // realtime data
    event(new CommonEvent($user->id));

    return back()->with('message', 'Customer updated');
  }

  /**
   * check last invoice number from Sale table
   * if not get any data then set 1
   * if get last invoice number then add 1 to
   * increase.
   */
  public function lastInvoice(Request $request)
  {
    $user       = Auth::user();
    $clientId   = $request->input('client_id');
    $currentYear = now('Asia/Dhaka')->format('Y');

    if (!$clientId) {
      return response()->json(['error' => 'BIN required']);
    }

    // LAST INVOICE FOR THIS BIN + YEAR
    $lastInvoice = $this->sale->query()
      ->when(!$this->hasRole(['admin']), fn($q) => $q->whereHas('client', fn($c) => $c->where('user_id', $user->id)))
      ->whereYear('date', $currentYear)
      ->where('client_id', $clientId)
      ->orderByDesc('id')
      ->first();

    if (!$lastInvoice) {
      $nextInvoice = '00001';
    } else {
      // Extract numeric part of last invoice
      // Assuming format INV-yy/00001
      $parts = explode('/', $lastInvoice->invoice_no);
      $number = isset($parts[1]) ? (int)$parts[1] : 0;
      $nextInvoice = str_pad($number + 1, 5, '0', STR_PAD_LEFT);
    }

    $invoice = 'INV-' . now('Asia/Dhaka')->format('y') . '/' . $nextInvoice;

    return response()->json(['lastinvoice' => $invoice]);
  }


  /**
   * This is for sale page. the limit can
   * change. so it comes from vatfactor table.
   */
  public function saleLimit()
  {
    $saleLimit = $this->vatfactor->where('name', 'sale_limit')->value('value');
    $note15 = $this->mushaknote->where('name_note_no', 'note_15')->value('percent');
    return response()->json(['salelimit'   => $saleLimit, 'note15' => $note15]);
  }
}
