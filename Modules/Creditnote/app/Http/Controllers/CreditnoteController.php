<?php

namespace Modules\Creditnote\Http\Controllers;

use App\Events\CommonEvent;
use App\Http\Controllers\Controller;
use App\Traits\ChecksUserRole;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Modules\Client\Models\Client;
use Modules\Creditnote\Models\Creditnote;
use Modules\Form43\Models\Form43;
use Modules\Form621\Models\Form621;
use Modules\Item\Models\Item;
use Modules\Sale\Models\Sale;
use Modules\Unit\Models\Unit;
use Throwable;

class CreditnoteController extends Controller
{
  use ChecksUserRole;

  public $client;
  public $item;
  public $unit;
  public $creditnote;
  public $form43;
  public $form621;
  public $sale;
  public function __construct(Client $client, Item $item, Unit $unit, Creditnote $creditnote, Form43 $form43, Form621 $form621, Sale $sale)
  {
    $this->client = $client;
    $this->item = $item;
    $this->unit = $unit;
    $this->creditnote = $creditnote;
    $this->form43 = $form43;
    $this->form621 = $form621;
    $this->sale = $sale;
  }

  /**
   * Display a listing of the resource.
   */
  public function index(Request $request)
  {
    $user   = Auth::user();
    $search = trim((string) $request->input('search', ''));
    $termLike = $search !== '' ? trim('%' . strtolower($search) . '%') : null;

    $creditnote = $this->creditnote->with(['client', 'client.user', 'client.user.role', 'item'])
      ->when(!$this->hasRole(['admin']), fn($query) => $query->whereHas('client', fn($q) => $q->where('user_id', $user->id)))
      ->when($search, function ($query) use ($termLike) {
        $query->where(function ($query) use ($termLike) {
          $query->where('lc_or_invoice_no', 'ilike', $termLike)
            ->orWhereHas('client', fn($q) => $q->whereRaw('search_text ILIKE ?', [$termLike]))
            ->orWhereHas('item', fn($q) => $q->whereRaw('search_text ILIKE ?', [$termLike]));
        });
      })->orderBy('id', 'desc')
      ->paginate((int)request('size', 10))
      ->withQueryString();

    return Inertia("creditnote/index", [
      'creditnote' => $creditnote,
      'search' => $search
    ]);
  }

  /**
   * credit note need only invoice -> stock plus
   */
  public function store(Request $request)
  {
    $user = Auth::user();

    $formField = $request->validate(
      [
        'lc_or_invoice_no' => ['required', 'string', 'max:20', function ($attribute, $value, $fail) {
          if (!Str::startsWith($value, 'INV')) {
            return $fail("Not invoice number");
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
        'issued_date' => ['required', 'date_format:"Y-m-d"', function ($attribute, $value, $fail) use ($request) {
          $date = Carbon::parse($value, 'Asia/Dhaka')->startOfDay();
          $today = Carbon::today('Asia/Dhaka')->startOfDay();

          // Stop if entry date is over current date (future date)
          if ($date->gt($today)) {
            return $fail("Greater than today");
          }

          $lastDate = $this->form621
            ->where('client_id', $request->input('client_id'))
            ->groupBy(['client_id'])
            ->max('date');

          if ($lastDate && ($lastDate > $value)) return $fail("Violate last date");
        }],
        'vat_challan_date' => ['required', 'date_format:"Y-m-d"', function ($attribute, $value, $fail) {
          $date = Carbon::parse($value, 'Asia/Dhaka')->startOfDay();
          $today = Carbon::today('Asia/Dhaka')->startOfDay();

          // Stop if entry date is over current date (future date)
          if ($date->gt($today)) {
            return $fail("Greater than today");
          }
        }],
        'value_in_challan' => 'required|numeric|gt:0',
        'quantity_in_challan' => 'required|numeric|gt:0',
        'unit_id' => ['required', 'numeric', 'gt:0', function ($attribute, $value, $fail) {
          $unit = $this->unit->find($value);
          if (!$unit) return $fail('Not exists');
        }],
        'vat_in_challan' => 'required|numeric|gte:0',
        'sd_in_challan' => 'required|numeric|gte:0',
        'value_of_decreasing_adjustment' => 'required|numeric|gt:0',
        'quantity_of_decreasing_adjustment' => 'required|numeric|gt:0',
        'vat_of_decreasing_adjustment' => 'required|numeric|gte:0',
        'sd_of_decreasing_adjustment' => 'required|numeric|gte:0',
        'reason_for_issuance' => 'required|max:150',
        'remarks' => 'nullable|max:250'
      ],
      [
        'lc_or_invoice_no.required' => 'Lc or invoice number required',
        'lc_or_invoice_no.max' => 'Max 50 char',
        'client_id.required' => 'BIN required',
        'client_id.numeric' => 'Number only',
        'client_id.gt' => 'Number > 0',
        'item_id.required' => 'Item required',
        'item_id.numeric' => 'Number only',
        'item_id.gt' => 'Number > 0',
        'issued_date.required' => 'Issue date required',
        'issued_date.date_format' => 'Invalid date',
        'vat_challan_date.required' => 'Challan date required',
        'vat_challan_date.date_format' => 'Invalid date',
        'value_in_challan.required' => 'Value required',
        'value_in_challan.numeric' => 'Number only',
        'value_in_challan.gt' => 'Number > 0',
        'quantity_in_challan.required' => 'Quantity required',
        'quantity_in_challan.numeric' => 'Number only',
        'quantity_in_challan.gt' => 'Number > 0',
        'unit_id.required' => 'Unit required',
        'unit_id.numeric' => 'Number only',
        'unit_id.gt' => 'Number > 0',
        'vat_in_challan.required' => 'VAT required',
        'vat_in_challan.numeric' => 'Number only',
        'vat_in_challan.gte' => '(+)Number only',
        'sd_in_challan.required' => 'SD required',
        'sd_in_challan.numeric' => 'Number only',
        'sd_in_challan.gte' => '(+)Number only',
        'value_of_decreasing_adjustment.required' => 'Decrease Value required',
        'value_of_decreasing_adjustment.numeric' => 'Number only',
        'value_of_decreasing_adjustment.gt' => 'Number > 0',
        'quantity_of_decreasing_adjustment.required' => 'Decrease quantity required',
        'quantity_of_decreasing_adjustment.numeric' => 'Number only',
        'quantity_of_decreasing_adjustment.gt' => 'Number > 0',
        'vat_of_decreasing_adjustment.required' => 'Decrease VAT required',
        'vat_of_decreasing_adjustment.numeric' => 'Number only',
        'vat_of_decreasing_adjustment.gte' => '(+)Number only',
        'sd_of_decreasing_adjustment.required' => 'Decrease SD required',
        'sd_of_decreasing_adjustment.numeric' => 'Number only',
        'sd_of_decreasing_adjustment.gte' => '(+)Number only',
        'reason_for_issuance.required' => 'Reason required',
        'reason_for_issuance.max' => 'Max 150 char',
        'remarks.max' => 'Max 250 char'
      ]
    );

    try {
      DB::transaction(function () use ($formField, $request, $user) {
        $currentYear = now('Asia/Dhaka')->format('Y');
        $clientId = $request->input('client_id');
        $itemId = $request->input('item_id');
        $invNo = $request->input('lc_or_invoice_no');

        $base621 = $this->form621->with(['sale'])->where('client_id', $clientId)->where('item_id', $itemId);
        $form621 = (clone $base621)->where('last', 1)->first();

        if (!$form621) {
          throw new \RuntimeException("Form 6.2.1 last not found");
        }

        $form43Id = (clone $base621)->where('lc_or_invoice_no', $invNo)->value('form43_id');

        if (!$form43Id) {
          throw new \RuntimeException("Form 4.3 reference id not found");
        }

        $saleUnitPrice = (clone $base621)->where('lc_or_invoice_no', $invNo)->first()?->sale?->unit_price;

        // Mark previous last as not last
        $form621->update(['last' => 0]);

        // LAST INVOICE FOR THIS BIN + THIS YEAR
        $lastInvoice = $this->creditnote->query()
          ->when(!$this->hasRole(['admin']), fn($q) => $q->whereHas('client', fn($q) => $q->where('user_id', $user->id)))
          ->where('client_id', $clientId)
          ->whereRaw('EXTRACT(YEAR FROM "issued_date"::date) = ?', [$currentYear])
          ->orderByDesc('id')
          ->first();

        if ($lastInvoice) {
          $parts = explode('/', $lastInvoice->creditnote_invoice);
          $number = isset($parts[1]) ? (int) $parts[1] : 0;
          $nextInvoiceNumber = str_pad($number + 1, 5, '0', STR_PAD_LEFT);
          $invoice = 'CRN-' . now('Asia/Dhaka')->format('y') . '/' . $nextInvoiceNumber;
        } else {
          $invoice = 'CRN-' . now('Asia/Dhaka')->format('y') . '/00001';
        }

        if ($formField['quantity_of_decreasing_adjustment'] > $formField['quantity_in_challan']) {
          throw new \RuntimeException("Over the quantity");
        }

        $stockAfter = (float) $form621->stock + (float) $formField['quantity_of_decreasing_adjustment'];

        $creditnote = $this->creditnote->create([
          'creditnote_invoice' => $invoice,
          'lc_or_invoice_no' => $formField['lc_or_invoice_no'],
          'client_id' => $clientId,
          'item_id' => $itemId,
          'issued_date' => $formField['issued_date'],
          'opening_stock' => number_format($form621->stock, 2, ".", ""),
          'stock' => number_format($stockAfter, 2, ".", ""),
          'vat_challan_date' => $formField['vat_challan_date'],
          'rate_in_challan' => number_format($saleUnitPrice, 2, ".", ""),
          'value_in_challan' => number_format($formField['value_in_challan'], 2, ".", ""),
          'quantity_in_challan' => number_format($formField['quantity_in_challan'], 2, ".", ""),
          'unit_id' => $formField['unit_id'],
          'vat_in_challan' => number_format($formField['vat_in_challan'], 2, ".", ""),
          'sd_in_challan' => number_format($formField['sd_in_challan'], 2, ".", ""),
          'value_of_decreasing_adjustment' => number_format($formField['value_of_decreasing_adjustment'], 2, ".", ""),
          'quantity_of_decreasing_adjustment' => number_format($formField['quantity_of_decreasing_adjustment'], 2, ".", ""),
          'vat_of_decreasing_adjustment' => number_format($formField['vat_of_decreasing_adjustment'], 2, ".", ""),
          'sd_of_decreasing_adjustment' => number_format($formField['sd_of_decreasing_adjustment'], 2, ".", ""),
          'reason_for_issuance' => $formField['reason_for_issuance'],
          'remarks' => $formField['remarks'],
        ]);

        $creditnote->form621()->create([
          'client_id'        => $clientId,
          'item_id'          => $itemId,
          'form43_id'        => $form43Id,
          'lc_or_invoice_no' => $invoice,
          'quantity'         => number_format($formField['quantity_of_decreasing_adjustment'], 2, ".", ""),
          'unit_id'          => $formField['unit_id'],
          'opening_stock'    => number_format($form621->stock, 2, ".", ""),
          'stock'            => number_format($stockAfter, 2, ".", ""),
          'date'             => $formField['issued_date'],
          'last'             => 1,
          'userid'           => $user->id,
        ]);
      });

      // realtime data
      event(new CommonEvent($user->id));

      return back()->with('message', 'Credit note created');
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

    $creditnote = $this->creditnote->with(['client.division', 'client.user', 'client.user.role', 'item', 'unit'])->find($id);

    if (!$this->hasRole(['admin']) && $creditnote?->client->user_id !== $user->id) {
      return response()->json(['error' => 'Unauthorised']);
    }

    return response()->json(['creditnote' => $creditnote]);
  }

  /**
   * credit note need only invoice -> stock plus
   */
  public function update(Request $request, $id)
  {
    $user = Auth::user();

    if (!$this->form621->where('importable_id', $id)->where('importable_type', 'mushak67')->where('last', 1)->first()) {
      throw new \RuntimeException("Can not update: not last combination");
    }

    $formField = $request->validate(
      [
        'lc_or_invoice_no' => ['required', 'string', 'max:20', function ($attribute, $value, $fail) {
          if (!Str::startsWith($value, 'INV')) {
            return $fail("Not invoice number");
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
        'issued_date' => ['required', 'date_format:"Y-m-d"', function ($attribute, $value, $fail) use ($request) {
          $date = Carbon::parse($value, 'Asia/Dhaka')->startOfDay();
          $today = Carbon::today('Asia/Dhaka')->startOfDay();

          // Stop if entry date is over current date (future date)
          if ($date->gt($today)) {
            return $fail("Greater than today");
          }

          $lastDate = $this->form621
            ->where('client_id', $request->input('client_id'))
            ->groupBy(['client_id'])
            ->max('date');

          if ($lastDate && ($lastDate > $value)) return $fail("Violate last date");
        }],
        'vat_challan_date' => ['required', 'date_format:"Y-m-d"', function ($attribute, $value, $fail) {
          $date = Carbon::parse($value, 'Asia/Dhaka')->startOfDay();
          $today = Carbon::today('Asia/Dhaka')->startOfDay();

          // Stop if entry date is over current date (future date)
          if ($date->gt($today)) {
            return $fail("Greater than today");
          }
        }],
        'value_in_challan' => 'required|numeric|gt:0',
        'quantity_in_challan' => 'required|numeric|gt:0',
        'unit_id' => ['required', 'numeric', 'gt:0', function ($attribute, $value, $fail) {
          $unit = $this->unit->find($value);
          if (!$unit) return $fail('Not exists');
        }],
        'vat_in_challan' => 'required|numeric|gte:0',
        'sd_in_challan' => 'required|numeric|gte:0',
        'value_of_decreasing_adjustment' => 'required|numeric|gt:0',
        'quantity_of_decreasing_adjustment' => 'required|numeric|gt:0',
        'vat_of_decreasing_adjustment' => 'required|numeric|gte:0',
        'sd_of_decreasing_adjustment' => 'required|numeric|gte:0',
        'reason_for_issuance' => 'required|max:150',
        'remarks' => 'nullable|max:250'
      ],
      [
        'lc_or_invoice_no.required' => 'Lc or invoice number required',
        'lc_or_invoice_no.max' => 'Max 50 char',
        'client_id.required' => 'BIN required',
        'client_id.numeric' => 'Number only',
        'client_id.gt' => 'Number > 0',
        'item_id.required' => 'Item required',
        'item_id.numeric' => 'Number only',
        'item_id.gt' => 'Number > 0',
        'issued_date.required' => 'Issue date required',
        'issued_date.date_format' => 'Invalid date',
        'vat_challan_date.required' => 'Challan date required',
        'vat_challan_date.date_format' => 'Invalid date',
        'value_in_challan.required' => 'Value required',
        'value_in_challan.numeric' => 'Number only',
        'value_in_challan.gt' => 'Number > 0',
        'quantity_in_challan.required' => 'Quantity required',
        'quantity_in_challan.numeric' => 'Number only',
        'quantity_in_challan.gt' => 'Number > 0',
        'unit_id.required' => 'Unit required',
        'unit_id.numeric' => 'Number only',
        'unit_id.gt' => 'Number > 0',
        'vat_in_challan.required' => 'VAT required',
        'vat_in_challan.numeric' => 'Number only',
        'vat_in_challan.gte' => '(+)Number only',
        'sd_in_challan.required' => 'SD required',
        'sd_in_challan.numeric' => 'Number only',
        'sd_in_challan.gte' => '(+)Number only',
        'value_of_decreasing_adjustment.required' => 'Decrease Value required',
        'value_of_decreasing_adjustment.numeric' => 'Number only',
        'value_of_decreasing_adjustment.gt' => 'Number > 0',
        'quantity_of_decreasing_adjustment.required' => 'Decrease quantity required',
        'quantity_of_decreasing_adjustment.numeric' => 'Number only',
        'quantity_of_decreasing_adjustment.gt' => 'Number > 0',
        'vat_of_decreasing_adjustment.required' => 'Decrease VAT required',
        'vat_of_decreasing_adjustment.numeric' => 'Number only',
        'vat_of_decreasing_adjustment.gte' => '(+)Number only',
        'sd_of_decreasing_adjustment.required' => 'Decrease SD required',
        'sd_of_decreasing_adjustment.numeric' => 'Number only',
        'sd_of_decreasing_adjustment.gte' => '(+)Number only',
        'reason_for_issuance.required' => 'Reason required',
        'reason_for_issuance.max' => 'Max 150 char',
        'remarks.max' => 'Max 250 char'
      ]
    );

    try {
      DB::transaction(function () use ($formField, $user, $id) {
        $creditnote = $this->creditnote->with('form621')->find($id);
        if (!$creditnote) {
          throw new \RuntimeException('Credit note not found');
        }

        $creditnoteForm621 = optional($creditnote->form621->first());
        if (!$creditnote) {
          throw new \RuntimeException('Related Form 6.2.1 row not found');
        }

        if ($formField['quantity_of_decreasing_adjustment'] > $formField['quantity_in_challan']) {
          throw new \RuntimeException("Over the quantity");
        }

        $stockAfter = (float) $creditnoteForm621->opening_stock + (float) $formField['quantity_of_decreasing_adjustment'];

        $creditnote->update([
          'issued_date' => $formField['issued_date'],
          'opening_stock' => number_format($creditnoteForm621->opening_stock, 2, ".", ""),
          'stock' => number_format($stockAfter, 2, ".", ""),
          'value_of_decreasing_adjustment' => number_format($formField['value_of_decreasing_adjustment'], 2, ".", ""),
          'quantity_of_decreasing_adjustment' => number_format($formField['quantity_of_decreasing_adjustment'], 2, ".", ""),
          'vat_of_decreasing_adjustment' => number_format($formField['vat_of_decreasing_adjustment'], 2, ".", ""),
          'sd_of_decreasing_adjustment' => number_format($formField['sd_of_decreasing_adjustment'], 2, ".", ""),
          'reason_for_issuance' => $formField['reason_for_issuance'],
          'remarks' => $formField['remarks'],
        ]);

        $creditnoteForm621->update([
          'quantity'         => number_format($formField['quantity_in_challan'], 2, ".", ""),
          'opening_stock'    => number_format($creditnoteForm621->opening_stock, 2, ".", ""),
          'stock'            => number_format($stockAfter, 2, ".", ""),
          'date'             => $formField['issued_date'],
          'userid'           => $user->id,
        ]);
      });

      // realtime data
      event(new CommonEvent($user->id));

      return back()->with('message', 'Credit note updated');
    } catch (Throwable $e) {
      return back()->with("error", $e->getMessage());
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

        $form621 = $this->form621->with(['client'])->where([['id', $id], ['importable_type', 'mushak67'], ['last', 1]]);

        if (!$form621->first()) {
          return back()->with('error', 'Can not delete: not last combination');
        }

        // temporary store data in variable
        $storeData = $form621->first();
        // delete current id
        $this->creditnote->where('id', $storeData->importable_id)->delete();
        $form621->delete();

        // set the latest remaining record as last=1 (if any)
        $newLastId = $this->form621->where('client_id', $storeData->client_id)->where('item_id', $storeData->item_id)->max('id');
        if ($newLastId) {
          $this->form621->where('id', $newLastId)->update(['last' => 1]);
        }
      });

      // realtime data
      event(new CommonEvent($user->id));

      return back()->with('message', 'Credit note deleted');
    } catch (Throwable $e) {
      return back()->with("error", $e->getMessage());
    }
  }
}
