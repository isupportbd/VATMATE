<?php

namespace Modules\Sale\Http\Controllers;

use App\Http\Controllers\Controller;
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
use Modules\Purchase\Models\Purchase;
use Modules\Sale\Models\Sale;
use Modules\Unit\Models\Unit;
use Modules\Vatfactor\Models\Vatfactor;
use Throwable;

class SaleController extends Controller
{
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
  public function __construct(Client $client, Sale $sale, Purchase $purchase, Form43 $form43, Form621 $form621, Item $item, Unit $unit, Vatfactor $vatfactor, Bank $bank, CustomerManagement $customer)
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
  }

  public function index(Request $request)
  {
    $user = Auth::user();
    $isAdmin = in_array(optional($user->role)->name, ['admin']);
    $search = $request->input('search');

    $sale = $this->sale->with(['client.division', 'client.user', 'client.user.role', 'item', 'unit', 'bank'])
      ->when(!$isAdmin, fn($query) => $query->whereHas('client', fn($q) => $q->where('user_id', $user->id)))
      ->when($search, function ($query) use ($search) {
        $query->where(function ($query) use ($search) {
          $query->whereHas('client', function ($query) use ($search) {
            $query->where('bin', 'like', '%' . strtolower($search) . '%')
              ->orWhere('company_name', 'like', '%' . strtolower($search) . '%')
              ->orWhere('mobile', 'like', '%' . strtolower($search) . '%');
          })->orWhereHas('item', fn($query) => $query->where('name', 'like', '%' . strtolower($search) . '%'));
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
    $isAdmin = in_array(optional($user->role)->name, ['admin']);

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

    // ─────────────────────────────────────────────
    // 2. GRAND TOTAL FOR WHOLE INVOICE
    //    (used for sale_limit check)
    // ─────────────────────────────────────────────
    $grandTotal = $items->reduce(function ($carry, $row) {
      $qty = (float) ($row['quantity'] ?? 0);
      $up  = (float) ($row['unit_price'] ?? 0);
      $sd  = (float) ($row['sd'] ?? 0);
      $vat = (float) ($row['vat'] ?? 0);

      $totalPrice = $qty * $up;
      $sdValue    = $totalPrice * ($sd / 100);
      $vatValue   = ($totalPrice + $sdValue) * ($vat / 100);

      return $carry + $totalPrice + $sdValue + $vatValue;
    }, 0.0);

    // ─────────────────────────────────────────────
    // 3. VALIDATION
    //    (header + each item)
    // ─────────────────────────────────────────────
    $saleLimit = $this->vatfactor->where('name', 'sale_limit')->value('value');
    $needPaymentInfo = $grandTotal > $saleLimit;

    $formField = $request->validate(
      [
        'client_id' => ['required', 'numeric', 'gt:0', function ($attribute, $value, $fail) use ($user, $isAdmin) {
          $client = $this->client->find($value);

          if (!$client) return $fail('Client does not exist');
          if ($client && $client->status == 0) return $fail('BIN inactive');
          if (!$isAdmin && $client && $client->user_id !== $user->id) return $fail('Unauthorised');
        }],
        'date' => ['required', 'date_format:"Y-m-d"', function ($attribute, $value, $fail) use ($request) {
          $entrydate = Carbon::parse($value, 'Asia/Dhaka')->startOfDay();
          $today     = Carbon::today('Asia/Dhaka')->startOfDay();

          if ($entrydate->gt($today)) return $fail("Greater than today");

          $lastDate = $this->form621->where('client_id', $request->input('client_id'))->groupBy(['client_id'])->max('date');

          if ($lastDate && ($lastDate > $value)) return $fail("Violate last date");
        }],
        'customer_name'    => 'nullable|max:100',
        'bin_or_nid'       => 'nullable|max:17',
        'address'          => 'nullable|max:150',
        'bank_id' => [$needPaymentInfo ? 'required' : 'nullable', 'numeric', 'gt:0', function ($attribute, $value, $fail) {
          /* $needPaymentInfo ? 'required' : 'nullable' */ // required only if exceed
          if ($value) {
            $bank = $this->bank->find($value);
            if (!$bank) return $fail('Not exists');
          }
        }],
        'transaction_no' => [$needPaymentInfo ? 'required' : 'nullable', 'max:50'],
        'transaction_date' => [$needPaymentInfo ? 'required' : 'nullable', 'date_format:Y-m-d'],
        'items' => ['required', 'array', 'min:1', function ($attribute, $value, $fail) {
          $itemIds = collect($value)->pluck('item_id');

          if ($itemIds->count() !== $itemIds->unique()->count()) {
            return $fail("Duplicate items found");
          }
        }],
        'items.*.item_id' => ['required', 'numeric', 'gt:0', function ($attribute, $value, $fail) use ($request) {
          $clientId = $request->input('client_id');
          if (!$clientId || !$value) return;

          // get latest purchase row (form621) for this client + item
          $purchaseRow = $this->form621->where('client_id', $clientId)->where('item_id', $value)->where('importable_type', 'purchase')->orderByDesc('id')->first();

          if (!$purchaseRow || !$purchaseRow->form43_id) {
            return $fail('6.2.1 has no 4.3 reference');
          }

          // load form43 with additions
          $form43 = $this->form43->with('additions')->find($purchaseRow->form43_id);

          if (!$form43) {
            return $fail('4.3 not found');
          }

          // addition check (no additions rows)
          if (!$form43->additions()->exists()) {
            $fail('4.3 has no addition');
          }
        }],
        'items.*.quantity' => ['required', 'numeric', 'gte:0', function ($attribute, $value, $fail) use ($request) {
          $clientId = $request->input('client_id');
          if (!$clientId) return;

          // $attribute will be like "items.0.quantity"
          if (!preg_match('/items\.(\d+)\.quantity/', $attribute, $m)) return;

          $index  = (int) $m[1];
          $itemId = data_get($request->input('items'), "$index.item_id"); // IMPORTANT: item_id

          if (!$itemId) return;

          $form621Last = $this->form621->where('client_id', $clientId)->where('item_id', $itemId)->where('last', 1)->first();

          if (!$form621Last) return $fail('Stock not found');
          if ($form621Last && ((float) $value > (float) $form621Last->stock)) return $fail("Over the stock");
        }],
        'items.*.unit_id'                   => 'required|numeric|gt:0',
        'items.*.unit_price'                => 'required|numeric|gt:0',
        'items.*.sd'                        => 'required|numeric|gte:0',
        'items.*.vat'                       => 'required|numeric|gte:0|between:0,15.00',
      ],
      [
        'client_id.required'                         => 'BIN required',
        'client_id.numeric'                          => 'Number only',
        'client_id.gt'                               => 'Number > 0',
        'date.required'                              => 'Date required',
        'date.date_format'                           => 'Invalid date',
        'customer_mobile.max'                        => 'Max 11 char',
        'customer_name.max'                          => 'Max 100 char',
        'bin_or_nid.max'                             => 'Max 17 char',
        'address.max'                                => 'Max 150 char',
        'bank_id.required'                           => 'Bank required',
        'bank_id.numeric'                            => 'Number only',
        'bank_id.gt'                                 => 'Number > 0',
        'transaction_no.required'                    => 'Transaction number required',
        'transaction_no.max'                         => 'Max 50 char',
        'transaction_date.required'                  => 'Transaction date required',
        'transaction_date.date_format'               => 'Invalid date',
        'items.required'                             => 'At least one item required',
        'items.array'                                => 'Invalid items data',
        'items.*.item_id.required'                   => 'Item required',
        'items.*.item_id.numeric'                    => 'Number only',
        'items.*.item_id.gt'                         => 'Number > 0',
        'items.*.unit_id.required'                   => 'Unit required',
        'items.*.unit_id.numeric'                    => 'Number only',
        'items.*.unit_id.gt'                         => 'Number > 0',
        'items.*.quantity.required'                  => 'Quantity required',
        'items.*.quantity.numeric'                   => 'Number only',
        'items.*.quantity.gte'                       => '(+)Number only',
        'items.*.unit_price.required'                => 'Unit price required',
        'items.*.unit_price.numeric'                 => 'Number only',
        'items.*.unit_price.gt'                      => 'Number > 0',
        'items.*.sd.required'                        => 'SD required',
        'items.*.sd.numeric'                         => 'Number only',
        'items.*.sd.gte'                             => '(+)Number only',
        'items.*.vat.required'                       => 'VAT required',
        'items.*.vat.numeric'                        => 'Number only',
        'items.*.vat.gte'                            => '(+)Number only',
        'items.*.vat.between'                        => 'Range 0 to 15',
      ]
    );

    try {
      DB::transaction(function () use ($user, $isAdmin, $clientId, $items, $date, $customerMobile, $customerName, $binOrNid, $address, $bankId, $transactionNo, $transactionDate) {
        $currentYear = now('Asia/Dhaka')->format('Y');

        // LAST INVOICE FOR THIS BIN + THIS YEAR
        $lastInvoice = $this->sale->query()
          ->when(!$isAdmin, fn($q) => $q->whereHas('client', fn($q) => $q->where('user_id', $user->id)))
          ->where('client_id', $clientId)
          ->whereYear('date', $currentYear)
          ->orderByDesc('id') // get last invoice
          ->first();

        if ($lastInvoice) {
          // extract numeric part after slash
          $parts = explode('/', $lastInvoice->invoice_no);
          $number = isset($parts[1]) ? (int)$parts[1] : 0;
          $nextInvoiceNumber = str_pad($number + 1, 5, '0', STR_PAD_LEFT);
          $invoice = 'INV-' . now('Asia/Dhaka')->format('y') . '/' . $nextInvoiceNumber;
        } else {
          $invoice = 'INV-' . now('Asia/Dhaka')->format('y') . '/00001';
        }

        // LOOP THROUGH ITEMS
        foreach ($items as $row) {
          $itemId   = $row['item_id'];
          $unitId   = $row['unit_id'];
          $qty      = (float) $row['quantity'];
          $up       = (float) $row['unit_price'];
          $sd       = (float) $row['sd'];
          $vat      = (float) $row['vat'];

          // backend calculations (ignore any totals from frontend)
          $totalPrice = $qty * $up;
          $sdValue    = $totalPrice * ($sd / 100);
          $vatValue   = ($totalPrice + $sdValue) * ($vat / 100);

          // LAST form_6.2.1 for this client + item
          $lastId = $this->form621->where('client_id', $clientId)->where('item_id', $itemId)->where('last', 1)->max('id');
          $form621Last = $lastId ? $this->form621->where('id', $lastId)->first() : null;

          if (!$form621Last) {
            // If for some reason there is no last 6.2.1, you may want to throw error
            throw new \RuntimeException("Stock not found for this item (client: $clientId, item: $itemId)");
          }

          // Mark previous last as not last
          $form621Last->update(['last' => 0]);

          // Get latest purchase row (form621) to fetch form43_id
          $purchaseRow = $this->form621->where('client_id', $clientId)->where('item_id', $itemId)->where('importable_type', 'purchase')->orderByDesc('id')->first();

          $purchase = $this->purchase->find($purchaseRow->importable_id);

          if (!$purchase || !$purchase->unit_price) {
            return back()->with('error', "No purchase data found for this item");
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
            'bank_id'                   => $bankId,
            'transaction_no'            => $transactionNo,
            'transaction_date'          => $transactionDate,
            'opening_stock'             => $form621Last->stock,
            'stock'                     => $stockAfter,
            'item_id'                   => $itemId,
            'unit_id'                   => $unitId,
            'quantity'                  => $qty,
            'last_purchase_unit_price'  => number_format($lastPurchaseUnitPrice, 2, '.', ''),
            'unit_price'                => number_format($up, 2, '.', ''),
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
            'form43_id'        => $purchaseRow->form43_id, // 👈 HERE
            'lc_or_invoice_no' => $invoice,
            'quantity'         => $qty,
            'unit_id'          => $unitId,
            'opening_stock'    => $form621Last->stock,
            'stock'            => $stockAfter,
            'date'             => $date,
            'last'             => 1,
            'userid'           => $user->id,
          ]);
        }
      });

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
    $isAdmin = in_array(optional($user->role)->name, ['admin']);

    $sale = $this->sale->with(['client.division', 'client.user.role', 'item', 'unit', 'bank'])->find($id);

    if (!$isAdmin && $sale && ($sale->client->user_id !== $user->id)) {
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
    $isAdmin = in_array(optional($user->role)->name, ['admin']);

    // load existing sale
    $sale = $this->sale->with('form621')->find($id);
    if (!$sale) {
      return back()->with('error', 'Sale not found');
    }

    // get the single form621 row created for this sale (assuming hasOne)
    $saleForm621 = optional($sale->form621->first()); // may be null if relation name differs; adjust as needed
    if (!$saleForm621) {
      return back()->with('error', 'Related form621 row not found');
    }

    // header/request values (single item)
    $clientId        = $request->input('client_id');
    $date            = $request->input('date');
    $customerMobile  = $request->input('customer_mobile');
    $customerName    = $request->input('customer_name');
    $binOrNid        = $request->input('bin_or_nid');
    $address         = $request->input('address');
    $bankId          = $request->input('bank_id');
    $transactionNo   = $request->input('transaction_no');
    $transactionDate = $request->input('transaction_date');

    // single item fields (the item should not be allowed to change ideally)
    $itemId    = $request->input('item_id');
    $unitId    = $request->input('unit_id');
    $quantity  = (float) $request->input('quantity', 0);
    $unitPrice = (float) $request->input('unit_price', 0);
    $sd        = (float) $request->input('sd', 0);
    $vat       = (float) $request->input('vat', 0);

    // compute totals for validation (single item)
    $totalPrice = $quantity * $unitPrice;
    $sdValue    = $totalPrice * ($sd / 100);
    $vatValue   = ($totalPrice + $sdValue) * ($vat / 100);
    $rowTotal   = $totalPrice + $sdValue + $vatValue;

    // sale limit check
    $saleLimit = $this->vatfactor->where('name', 'sale_limit')->value('value');
    $needPaymentInfo = ($rowTotal > $saleLimit);

    // Validate request - inline validators included
    $formField = $request->validate(
      [
        // client must match existing sale's client
        'client_id' => ['required', 'numeric', 'gt:0', function ($attribute, $value, $fail) use ($sale, $user, $isAdmin) {
          $client = $this->client->find($value);
          if (!$client) return $fail('Client does not exist');
          if ($client->status == 0) return $fail('BIN inactive');
          if (!$isAdmin && $client->user_id !== $user->id) return $fail('Unauthorised');
          // ensure that client_id is not changed
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

          // do not allow changing item
          if ($value != $sale->item_id) return $fail('Not changable');

          // find last form621 for this client+item
          $lastForm621Id = $this->form621->where('client_id', $sale->client_id)->where('item_id', $value)->where('last', 1)->max('id');

          if (!$lastForm621Id) return $fail('Not last combination');

          // ensure the sale's form621 is the last one (editable)
          if (optional($sale->form621->first())->id != $lastForm621Id) {
            return $fail('Not editable');
          }

          // check form43 (from the last purchase row) and its additions if needed
          $purchaseRow = $this->form621->where('client_id', $sale->client_id)->where('item_id', $value)->where('importable_type', 'purchase')->orderByDesc('id')->first();

          if (!$purchaseRow || !$purchaseRow->form43_id) return $fail('4.3 not found');

          $form43 = $this->form43->with('additions')->find($purchaseRow->form43_id);

          if (!$form43) return $fail('4.3 not found');

          if ($form43->additions->isEmpty()) return $fail('4.3 has no addition');
        }],
        'unit_id' => 'required|numeric|gt:0',
        'quantity' => ['required', 'numeric', 'gte:0', function ($attribute, $value, $fail) use ($sale) {
          $opening = (float) optional($sale->form621->first())->opening_stock;
          if ((float)$value > $opening) return $fail('Over the stock');
        }],
        'unit_price' => 'required|numeric|gt:0',
        'sd'         => 'required|numeric|gte:0',
        'vat'        => 'required|numeric|gte:0|between:0,15.00',
      ],
      [
        'client_id.required' => 'BIN required',
        'client_id.numeric'  => 'Number only',
        'client_id.gt'       => 'Number > 0',
        'date.required'      => 'Date required',
        'date.date_format'   => 'Invalid date',
        'customer_name.max'  => 'Max 100 char',
        'bin_or_nid.max'     => 'Max 17 char',
        'address.max'        => 'Max 150 char',
        'bank_id.required'   => 'Bank required',
        'bank_id.numeric'    => 'Number only',
        'bank_id.gt'         => 'Number > 0',
        'transaction_no.required' => 'Transaction number required',
        'transaction_no.max' => 'Max 50 char',
        'transaction_date.required' => 'Transaction date required',
        'transaction_date.date_format' => 'Invalid date',
        'item_id.required'   => 'Item required',
        'item_id.numeric'    => 'Number only',
        'item_id.gt'         => 'Number > 0',
        'unit_id.required'   => 'Unit required',
        'unit_id.numeric'    => 'Number only',
        'unit_id.gt'         => 'Number > 0',
        'quantity.required'  => 'Quantity required',
        'quantity.numeric'   => 'Number only',
        'quantity.gte'       => '(+)Number only',
        'unit_price.required' => 'Unit price required',
        'unit_price.numeric' => 'Number only',
        'unit_price.gt'      => 'Number > 0',
        'sd.required'        => 'SD required',
        'sd.numeric'         => 'Number only',
        'sd.gte'             => '(+)Number only',
        'vat.required'       => 'VAT required',
        'vat.numeric'        => 'Number only',
        'vat.gte'            => '(+)Number only',
        'vat.between'        => 'Range 0 to 15',
      ]
    );

    try {
      DB::transaction(function () use ($user, $sale, $saleForm621, $date, $customerMobile, $customerName, $binOrNid, $address, $bankId, $transactionNo, $transactionDate, $unitId, $quantity, $unitPrice, $sd, $vat, $totalPrice, $sdValue, $vatValue) {
        // compute derived values
        $stockAfter = ((float)$saleForm621->opening_stock) - (float)$quantity;

        // update sale (we do NOT update client_id, item_id, invoice_no, form43_id, last_purchase_unit_price)
        $sale->update([
          'date'                     => $date,
          'customer_mobile'          => $customerMobile,
          'customer_name'            => $customerName,
          'bin_or_nid'               => $binOrNid,
          'address'                  => $address,
          'bank_id'                  => $bankId,
          'transaction_no'           => $transactionNo,
          'transaction_date'         => $transactionDate,
          'opening_stock'            => $saleForm621->opening_stock,
          'stock'                    => $stockAfter,
          'unit_id'                  => $unitId,
          'quantity'                 => number_format($quantity, 3, '.', ''),
          'unit_price'               => number_format($unitPrice, 2, '.', ''),
          'total_price'              => number_format($totalPrice, 2, '.', ''),
          'sd'                       => number_format($sd, 2, '.', ''),
          'sd_value'                 => number_format($sdValue, 2, '.', ''),
          'vat'                      => number_format($vat, 2, '.', ''),
          'vat_value'                => number_format($vatValue, 2, '.', ''),
        ]);

        // update existing form621 row (do not change form43_id, importable_type/id, last flag)
        $saleForm621->update([
          'quantity'      => number_format($quantity, 3, '.', ''),
          'unit_id'       => $unitId,
          'opening_stock' => $saleForm621->opening_stock,
          'stock'         => $stockAfter,
          'date'          => $date,
          'userid'        => $user->id,
        ]);
      });

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
    $isAdmin = in_array(optional($user->role)->name, ['admin']);

    try {
      DB::transaction(function () use ($id, $user, $isAdmin) {
        $permission = $this->form621->with(['client'])->where('id', $id)
          ->when(!$isAdmin, fn($q) => $q->whereHas('client', fn($c) => $c->where('user_id', $user->id)))
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

      DB::commit();
      return back()->with('message', 'Sale deleted');
    } catch (Throwable $e) {
      DB::rollBack();
      return back()->with("error", $e->getMessage());
    }
  }

  // function call from purchase page
  public function updateCustomer(Request $request, $id)
  {
    $user = Auth::user();
    $isAdmin = in_array(optional($user->role)->name, ['admin']);

    $sale = $this->sale->with(['client'])->where('id', $id)->first();

    if (!$isAdmin && ($sale->client->user_id !== $user->id)) {
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
    $isAdmin    = in_array(optional($user->role)->name, ['admin']);
    $clientId   = $request->input('client_id');
    $currentYear = now('Asia/Dhaka')->format('Y');

    if (!$clientId) {
      return response()->json(['error' => 'BIN required']);
    }

    // LAST INVOICE FOR THIS BIN + YEAR
    $lastInvoice = $this->sale->query()
      ->when(!$isAdmin, fn($q) => $q->whereHas('client', fn($c) => $c->where('user_id', $user->id)))
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
    return response()->json(['salelimit'   => $saleLimit]);
  }
}
