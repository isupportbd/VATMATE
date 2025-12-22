<?php

namespace Modules\Form45\Http\Controllers;

use App\Events\CommonEvent;
use App\Http\Controllers\Controller;
use App\Traits\ChecksUserRole;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Client\Models\Client;
use Modules\Form45\Models\Form45;
use Modules\Form621\Models\Form621;
use Modules\Item\Models\Item;
use Modules\Mushaknote\Models\Mushaknote;
use Modules\Rate\Models\Rate;
use Modules\Unit\Models\Unit;
use Throwable;

class Form45Controller extends Controller
{
  use ChecksUserRole;

  public $form45;
  public $client;
  public $item;
  public $form621;
  public $unit;
  public $mushaknote;
  public $rate;
  public function __construct(Form45 $form45, Client $client, Item $item, Form621 $form621, Unit $unit, Mushaknote $mushaknote, Rate $rate)
  {
    $this->form45 = $form45;
    $this->client = $client;
    $this->item = $item;
    $this->form621 = $form621;
    $this->unit = $unit;
    $this->mushaknote = $mushaknote;
    $this->rate = $rate;
  }

  public function index(Request $request)
  {
    $user = Auth::user();
    $search = trim((string) $request->input('search', ''));
    $termLike   = $search !== '' ? $search . '%' : null;

    $form45 = $this->form45->with(['client.division', 'client.user', 'client.user.role', 'item', 'unit'])
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

    $result = $form45->orderBy('id', 'desc')->paginate((int)request('size', 10))->withQueryString();

    return Inertia("form45/index", ['form45' => $result, 'search' => $search]);
  }

  /**
   * 4.4 need lc and boe -> stock minus
   */
  public function store(Request $request)
  {
    $user    = Auth::user();

    // 1️ FIRST: FIELD VALIDATION
    $formField = $request->validate(
      [
        'client_id' => ['required', 'numeric', 'gt:0', function ($attribute, $value, $fail) use ($user) {
          $client = $this->client->find($value);
          if (!$client) return $fail('Client does not exist');
          if ($client->status == 0) return $fail('BIN inactive');
          if (!$this->hasRole(['admin']) && $client->user_id !== $user->id) return $fail('Unauthorised');
        }],
        'lc_or_invoice_no' => ['required', 'string', 'max:20', function ($attribute, $value, $fail) {
          $invoicePrefixes = collect(['INV', 'CRN', 'DBN', 'F44', 'F45']);
          $isInvoice = $invoicePrefixes->contains(fn($prefix) => Str::startsWith($value, $prefix));
          if (!$isInvoice && !ctype_digit($value)) return $fail("Not LC number");
        }],
        'item_id' => ['required', 'numeric', 'gt:0', function ($attribute, $value, $fail) {
          $item = $this->item->find($value);
          if (!$item) return $fail('Not exists');
        }],
        'boe' => 'required|string|max:20',
        'unit_id' => ['required', 'numeric', 'gt:0', function ($attribute, $value, $fail) {
          $unit = $this->unit->find($value);
          if (!$unit) return $fail('Not exists');
        }],
        'date' => ['required', 'date_format:"Y-m-d"', function ($attribute, $value, $fail) {
          $clientId = request('client_id'); // from request
          $date     = Carbon::parse($value, 'Asia/Dhaka')->startOfDay();
          $today    = Carbon::today('Asia/Dhaka')->startOfDay();
          $lastDate = $this->form621->where('client_id', $clientId)->groupBy(['client_id'])->max('date');

          if ($date->gt($today)) return $fail("Greater than today");
          if ($lastDate && ($lastDate > $value)) return $fail("Violate last date");
        }],
        'quantity' => 'required|numeric|gt:0',
        'value'    => 'required|numeric|gt:0',
        'vat'      => 'required|numeric|gte:0',
        'proposed_value' => ['required', 'numeric', 'gte:0'],
        'increasing_adjustment_vat' => 'required|numeric|gte:0',
        'net_increase'              => 'required|numeric|gte:0',
        'reason_ineligibility'      => 'required|max:150',
      ],
      [
        'client_id.required' => 'BIN required',
        'client_id.numeric'  => 'Number only',
        'client_id.gt'       => 'Number > 0',
        'lc_or_invoice_no.required' => 'LC number required',
        'lc_or_invoice_no.max'      => 'Max 20 char',
        'item_id.required'   => 'Item required',
        'item_id.numeric'    => 'Number only',
        'item_id.gt'         => 'Number > 0',
        'boe.required'       => 'BoE required',
        'boe.max'            => 'Max 10 char',
        'unit_id.required'   => 'Unit required',
        'unit_id.numeric'    => 'Number only',
        'unit_id.gt'         => 'Number > 0',
        'date.required'      => 'Date required',
        'date.date_format'   => 'Invalid date',
        'quantity.required'  => 'Quantity required',
        'quantity.numeric'   => 'Number only',
        'quantity.gt'        => 'Number > 0',
        'value.required'     => 'Value required',
        'value.numeric'      => 'Number only',
        'value.gt'           => 'Number > 0',
        'vat.required'       => 'VAT required',
        'vat.numeric'        => 'Number only',
        'vat.gte'            => '(+)Number only',
        'proposed_value.required' => 'Proposed value required',
        'proposed_value.numeric'  => 'Number only',
        'proposed_value.gte'      => '(+)Number only',
        'increasing_adjustment_vat.required' => 'Adjustment required',
        'increasing_adjustment_vat.numeric'  => 'Number only',
        'increasing_adjustment_vat.gte'      => '(+)Number only',
        'net_increase.required'  => 'Net increase required',
        'net_increase.numeric'   => 'Number only',
        'net_increase.gte'       => '(+)Number only',
        'reason_ineligibility.required' => 'Reason required',
        'reason_ineligibility.max'      => 'Max 150 char',
      ]
    );

    // 2️ AFTER FIELD VALIDATION PASSES → DOMAIN CHECKS
    $clientId = $formField['client_id'];
    $itemId   = $formField['item_id'];
    $invNo    = $formField['lc_or_invoice_no'];
    $boe      = $formField['boe'];

    $base621 = $this->form621->with(['purchase', 'client', 'item', 'form43', 'unit', 'user'])->where('client_id', $clientId);
    $form621 = (clone $base621)->where('item_id', $itemId)->where('lc_or_invoice_no', $invNo)->where('boe', $boe)->first();
    $form621lastStock = (clone $base621)->select('id', 'opening_stock', 'stock', 'last')->where('item_id', $itemId)->where('last', 1)->first();
    $note15   = $this->mushaknote->where('name_note_no', 'note_15')->value('percent');
    $rateLast = $this->rate->where('client_id', $clientId)->where('item_id', $itemId)->where('last', 1)->first();

    if (!$form621) {
      return back()->withErrors(['lc_or_invoice_no' => 'Form 6.2.1 not found'])->withInput();
    }

    if (!$rateLast) {
      return back()->withErrors(['proposed_value' => 'Last rate not found client item combination'])->withInput();
    }

    if (!$form621lastStock) {
      return back()->withErrors(['quantity' => 'Stock not found'])->withInput();
    }

    // 3 Extra field-level rules based on DB data (stock & price)
    $pv  = (float) $formField['proposed_value'];
    $qty = (float) $formField['quantity'];

    $pup  = (float) $form621?->purchase?->unit_price;
    $psd  = (float) $form621?->purchase?->sd;
    $pvat = (float) $form621?->purchase?->vat;
    $rsd  = (float) $rateLast->sd;
    $rvat = (float) $rateLast->vat;

    if ($qty > $form621lastStock->stock) {
      return back()->withErrors(['quantity' => 'Over the stock'])->withInput();
    }

    if ($pv > $pup) {
      return back()->withErrors(['proposed_value' => 'Over the actual price'])->withInput();
    }

    // 4️ Now safe to compute all derived values
    $price    = $qty * $pup;
    $sdValue  = $price  * ($psd / 100);
    $vatValue = ($price + $sdValue) * ($pvat / 100);
    $adj      = $qty * $pv;
    $pvsd     = $adj * ($rsd / 100);
    $pvvat    = ($adj + $pvsd) * ($rvat / 100);

    if ((float) $note15 === (float) $rvat) {
      $incadj = $pvvat;
      $netinc = $vatValue - $pvvat;
    } else {
      $incadj = $pvvat;
      $netinc = $pvvat; // or whatever you need here
    }

    try {
      DB::transaction(function () use ($user, $formField, $clientId, $form621, $form621lastStock, $price, $vatValue, $incadj, $netinc) {
        $currentYear = now('Asia/Dhaka')->format('Y');

        // Mark previous last as not last
        $form621lastStock->update(['last' => 0]);

        // LAST INVOICE FOR THIS BIN + THIS YEAR
        $lastInvoice = $this->form45->where('client_id', $clientId)->whereYear('date', $currentYear)->orderByDesc('id')->first();
        if ($lastInvoice) {
          $parts  = explode('/', $lastInvoice->invoice_no);
          $number = isset($parts[1]) ? (int) $parts[1] : 0;
          $nextInvoiceNumber = str_pad($number + 1, 5, '0', STR_PAD_LEFT);
          $invoice = 'F45-' . now('Asia/Dhaka')->format('y') . '/' . $nextInvoiceNumber;
        } else {
          $invoice = 'F45-' . now('Asia/Dhaka')->format('y') . '/00001';
        }

        $stockAfter = (float) $form621lastStock->stock - (float) $formField['quantity'];

        // remove calculated fields from $formField (optional)
        $formField = Arr::except($formField, ['stock', 'value', 'vat', 'increasing_adjustment_vat', 'net_increase']);

        $form45 = $this->form45->create([
          'form45_invoice'            => $invoice,
          'client_id'                 => $formField['client_id'],
          'lc_or_invoice_no'          => $formField['lc_or_invoice_no'],
          'item_id'                   => $formField['item_id'],
          'boe'                       => $formField['boe'],
          'unit_id'                   => $formField['unit_id'],
          'form621_id'                => $form621->id,
          'date'                      => $formField['date'],
          'opening_stock'             => number_format($form621lastStock->stock, 2, ".", ""),
          'stock'                     => number_format($stockAfter, 2, ".", ""),
          'quantity'                  => number_format($formField['quantity'], 2, ".", ""),
          'value'                     => number_format($price, 2, ".", ""),
          'vat'                       => number_format($vatValue, 2, ".", ""),
          'proposed_value'            => number_format($formField['proposed_value'], 2, ".", ""),
          'increasing_adjustment_vat' => number_format($incadj, 2, ".", ""),
          'net_increase'              => number_format($netinc, 2, ".", ""),
          'reason_ineligibility'      => $formField['reason_ineligibility'],
        ]);

        $form45->form621()->create([
          'client_id'        => $formField['client_id'],
          'item_id'          => $formField['item_id'],
          'form43_id'        => $form621->form43_id,
          'lc_or_invoice_no' => $invoice,
          'boe'              => $formField['boe'],
          'quantity'         => number_format($formField['quantity'], 2, ".", ""),
          'unit_id'          => $formField['unit_id'],
          'opening_stock'    => number_format($form621lastStock->stock, 2, ".", ""),
          'stock'            => number_format($stockAfter, 2, ".", ""),
          'date'             => $formField['date'],
          'last'             => 1,
          'userid'           => $user->id,
        ]);
      });

      // realtime data
      event(new CommonEvent($user->id));

      return back()->with('message', 'Form 4.4 created');
    } catch (\Throwable $e) {
      return back()->with('error', $e->getMessage());
    }
  }


  /**
   * Show the specified resource.
   */
  public function show($id)
  {
    $user = Auth::user();

    $form45 = $this->form45->with(['client.division', 'client.user', 'client.user.role', 'item', 'unit'])->find($id);

    if (!$this->hasRole(['admin']) && $form45?->client->user_id !== $user->id) {
      return response()->json(['error' => 'Unauthorised']);
    }

    return response()->json(['form45' => $form45]);
  }

  /**
   * 4.4 need lc and boe -> stock minus
   */
  public function update(Request $request, $id)
  {
    $user    = Auth::user();

    // 1️ FIRST: FIELD VALIDATION
    $formField = $request->validate(
      [
        'client_id' => ['required', 'numeric', 'gt:0', function ($attribute, $value, $fail) use ($user) {
          $client = $this->client->find($value);
          if (!$client) return $fail('Client does not exist');
          if ($client->status == 0) return $fail('BIN inactive');
          if (!$this->hasRole(['admin']) && $client->user_id !== $user->id) return $fail('Unauthorised');
        }],
        'lc_or_invoice_no' => ['required', 'string', 'max:20', function ($attribute, $value, $fail) {
          $invoicePrefixes = collect(['INV', 'CRN', 'DBN', 'F44', 'F45']);
          $isInvoice = $invoicePrefixes->contains(fn($prefix) => Str::startsWith($value, $prefix));
          if (!$isInvoice && !ctype_digit($value)) return $fail("Not LC number");
        }],
        'item_id' => ['required', 'numeric', 'gt:0', function ($attribute, $value, $fail) {
          $item = $this->item->find($value);
          if (!$item) return $fail('Not exists');
        }],
        'boe' => 'required|string|max:20',
        'unit_id' => ['required', 'numeric', 'gt:0', function ($attribute, $value, $fail) {
          $unit = $this->unit->find($value);
          if (!$unit) return $fail('Not exists');
        }],
        'date' => ['required', 'date_format:"Y-m-d"', function ($attribute, $value, $fail) {
          $clientId = request('client_id'); // from request
          $date     = Carbon::parse($value, 'Asia/Dhaka')->startOfDay();
          $today    = Carbon::today('Asia/Dhaka')->startOfDay();
          $lastDate = $this->form621->where('client_id', $clientId)->groupBy(['client_id'])->max('date');

          if ($date->gt($today)) return $fail("Greater than today");
          if ($lastDate && ($lastDate > $value)) return $fail("Violate last date");
        }],
        'quantity' => 'required|numeric|gt:0',
        'value'    => 'required|numeric|gt:0',
        'vat'      => 'required|numeric|gte:0',
        'proposed_value' => ['required', 'numeric', 'gte:0'],
        'increasing_adjustment_vat' => 'required|numeric|gte:0',
        'net_increase'              => 'required|numeric|gte:0',
        'reason_ineligibility'      => 'required|max:150',
      ],
      [
        'client_id.required' => 'BIN required',
        'client_id.numeric'  => 'Number only',
        'client_id.gt'       => 'Number > 0',
        'lc_or_invoice_no.required' => 'LC number required',
        'lc_or_invoice_no.max'      => 'Max 20 char',
        'item_id.required'   => 'Item required',
        'item_id.numeric'    => 'Number only',
        'item_id.gt'         => 'Number > 0',
        'boe.required'       => 'BoE required',
        'boe.max'            => 'Max 10 char',
        'unit_id.required'   => 'Unit required',
        'unit_id.numeric'    => 'Number only',
        'unit_id.gt'         => 'Number > 0',
        'date.required'      => 'Date required',
        'date.date_format'   => 'Invalid date',
        'quantity.required'  => 'Quantity required',
        'quantity.numeric'   => 'Number only',
        'quantity.gt'        => 'Number > 0',
        'value.required'     => 'Value required',
        'value.numeric'      => 'Number only',
        'value.gt'           => 'Number > 0',
        'vat.required'       => 'VAT required',
        'vat.numeric'        => 'Number only',
        'vat.gte'            => '(+)Number only',
        'proposed_value.required' => 'Proposed value required',
        'proposed_value.numeric'  => 'Number only',
        'proposed_value.gte'      => '(+)Number only',
        'increasing_adjustment_vat.required' => 'Adjustment required',
        'increasing_adjustment_vat.numeric'  => 'Number only',
        'increasing_adjustment_vat.gte'      => '(+)Number only',
        'net_increase.required'  => 'Net increase required',
        'net_increase.numeric'   => 'Number only',
        'net_increase.gte'       => '(+)Number only',
        'reason_ineligibility.required' => 'Reason required',
        'reason_ineligibility.max'      => 'Max 150 char',
      ]
    );

    // 2️ AFTER FIELD VALIDATION PASSES → DOMAIN CHECKS
    $clientId = $formField['client_id'];
    $itemId   = $formField['item_id'];
    $invNo    = $formField['lc_or_invoice_no'];
    $boe      = $formField['boe'];

    $base621 = $this->form621->with(['purchase', 'client', 'item', 'form43', 'unit', 'user'])->where('client_id', $clientId);
    $form621 = (clone $base621)->where('item_id', $itemId)->where('lc_or_invoice_no', $invNo)->where('boe', $boe)->first();
    $form621lastStock = (clone $base621)->select('id', 'opening_stock', 'stock', 'last')->where('item_id', $itemId)->where('last', 1)->first();
    $note15   = $this->mushaknote->where('name_note_no', 'note_15')->value('percent');
    $rateLast = $this->rate->where('client_id', $clientId)->where('item_id', $itemId)->where('last', 1)->first();

    if (!$form621) {
      return back()->withErrors(['lc_or_invoice_no' => 'Form 6.2.1 not found'])->withInput();
    }

    if (!$rateLast) {
      return back()->withErrors(['proposed_value' => 'Last rate not found client item combination'])->withInput();
    }

    if (!$form621lastStock) {
      return back()->withErrors(['quantity' => 'Stock not found'])->withInput();
    }

    // 3 Extra field-level rules based on DB data (stock & price)
    $pv  = (float) $formField['proposed_value'];
    $qty = (float) $formField['quantity'];

    $pup  = (float) $form621?->purchase?->unit_price;
    $psd  = (float) $form621?->purchase?->sd;
    $pvat = (float) $form621?->purchase?->vat;
    $rsd  = (float) $rateLast->sd;
    $rvat = (float) $rateLast->vat;

    if ($qty > $form621lastStock->opening_stock) {
      return back()->withErrors(['quantity' => 'Over the stock'])->withInput();
    }

    if ($pv > $pup) {
      return back()->withErrors(['proposed_value' => 'Over the actual price'])->withInput();
    }

    // 4️ Now safe to compute all derived values
    $price    = $qty * $pup;
    $sdValue  = $price  * ($psd / 100);
    $vatValue = ($price + $sdValue) * ($pvat / 100);
    $adj      = $qty * $pv;
    $pvsd     = $adj * ($rsd / 100);
    $pvvat    = ($adj + $pvsd) * ($rvat / 100);

    if ((float) $note15 === (float) $rvat) {
      $incadj = $pvvat;
      $netinc = $vatValue - $pvvat;
    } else {
      $incadj = $pvvat;
      $netinc = $pvvat; // or whatever you need here
    }

    try {
      DB::transaction(function () use ($id, $user, $formField, $clientId, $form621, $form621lastStock, $price, $vatValue, $incadj, $netinc) {
        $form45 = $this->form45->with('form621')->find($id);
        if (!$form45) {
          throw new \RuntimeException('Form 4.4 not found');
        }

        $form45Form621 = optional($form45->form621->first());
        if (!$form45Form621) {
          throw new \RuntimeException('Related Form 6.2.1 row not found');
        }

        $stockAfter = (float) $form621lastStock->opening_stock - (float) $formField['quantity'];

        // remove calculated fields from $formField (optional)
        $formField = Arr::except($formField, ['stock', 'value', 'vat', 'increasing_adjustment_vat', 'net_increase']);

        $form45->update([
          'boe'                       => $formField['boe'],
          'date'                      => $formField['date'],
          'opening_stock'             => number_format($form621lastStock->opening_stock, 2, ".", ""),
          'stock'                     => number_format($stockAfter, 2, ".", ""),
          'quantity'                  => number_format($formField['quantity'], 2, ".", ""),
          'value'                     => number_format($price, 2, ".", ""),
          'vat'                       => number_format($vatValue, 2, ".", ""),
          'proposed_value'            => number_format($formField['proposed_value'], 2, ".", ""),
          'increasing_adjustment_vat' => number_format($incadj, 2, ".", ""),
          'net_increase'              => number_format($netinc, 2, ".", ""),
          'reason_ineligibility'      => $formField['reason_ineligibility'],
        ]);

        $form45Form621->update([
          'boe'              => $formField['boe'],
          'quantity'         => number_format($formField['quantity'], 2, ".", ""),
          'opening_stock'    => number_format($form621lastStock->opening_stock, 2, ".", ""),
          'stock'            => number_format($stockAfter, 2, ".", ""),
          'date'             => $formField['date'],
          'userid'           => $user->id,
        ]);
      });

      // realtime data
      event(new CommonEvent($user->id));

      return back()->with('message', 'Form 4.4 updated');
    } catch (\Throwable $e) {
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

        $form621 = $this->form621->with(['client'])->where([['id', $id], ['importable_type', 'mushak45'], ['last', 1]]);

        if (!$form621->first()) {
          return back()->with('error', 'Can not delete: not last combination');
        }

        // temporary store data in variable
        $storeData = $form621->first();
        // delete current id
        $this->form45->where('id', $storeData->importable_id)->delete();
        $form621->delete();

        // set the latest remaining record as last=1 (if any)
        $newLastId = $this->form621->where('client_id', $storeData->client_id)->where('item_id', $storeData->item_id)->max('id');
        if ($newLastId) {
          $this->form621->where('id', $newLastId)->update(['last' => 1]);
        }
      });

      // realtime data
      event(new CommonEvent($user->id));

      return back()->with('message', 'Form 4.4 deleted');
    } catch (Throwable $e) {
      return back()->with("error", $e->getMessage());
    }
  }
}
