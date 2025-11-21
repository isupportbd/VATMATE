<?php

namespace Modules\Creditnote\Http\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Modules\Client\Models\Client;
use Modules\Creditnote\Models\Creditnote;
use Modules\Item\Models\Item;
use Modules\Unit\Models\Unit;
use Psy\Util\Str;
use Throwable;

class CreditnoteController extends Controller
{
  public $client;
  public $item;
  public $unit;
  public $creditnote;
  public function __construct(Client $client, Item $item, Unit $unit, Creditnote $creditnote)
  {
    $this->client = $client;
    $this->item = $item;
    $this->unit = $unit;
    $this->creditnote = $creditnote;
  }

  /**
   * Display a listing of the resource.
   */
  public function index(Request $request)
  {
    $user   = Auth::user();
    $isAdmin = in_array(optional($user->role)->name, ['admin']);
    $search = trim((string) $request->input('search', ''));

    $creditnote = $this->creditnote->with(['client', 'client.user', 'client.user.role', 'item'])
      // ->when(!$isAdmin, fn($query) => $query->whereHas('client', fn($q) => $q->where('user_id', $user->id)))
      ->when($search, function ($query) use ($search) {
        $query->where(function ($query) use ($search) {
          $query->where('lc_or_invoice_no', 'like', "%{$search}%")
            ->orWhereHas('client', function ($query) use ($search) {
              $query->where('company_name', 'like', "%{$search}%")
                ->orWhere('mobile', 'like', "%{$search}%")
                ->orWhere('bin', 'like', "%{$search}%");
            })->orWhereHas('item', function ($query) use ($search) {
              $query->where('name', 'like', "%{$search}%")
                ->orWhere('hs_code', 'like', "%{$search}%");
            });
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
    $isAdmin = in_array(optional($user->role)->name, ['admin']);

    $formField = $request->validate(
      [
        'lc_or_invoice_no' => ['required', 'string', 'max:20', function ($attribute, $value, $fail) {
          if (!Str::startsWith($value, 'INV')) {
            return $fail("Not invoice number");
          }
        }],
        'client_id' => ['required', 'numeric', 'gt:0', function ($attribute, $value, $fail) use ($user, $isAdmin) {
          $client = $this->client->find($value);

          if (!$client) return $fail('Client does not exist');

          if ($client->status == 0) return $fail('BIN inactive');

          if (!$isAdmin && $client->user_id !== $user->id) return $fail('Unauthorised');
        }],
        'item_id' => ['required', 'numeric', 'gt:0', function ($attribute, $value, $fail) {
          $item = $this->item->find($value);
          if (!$item) return $fail('Not exists');
        }],
        'issued_date' => ['required', 'date_format:"Y-m-d"', function ($attribute, $value, $fail) {
          $date = Carbon::parse($value, 'Asia/Dhaka')->startOfDay();
          $today = Carbon::today('Asia/Dhaka')->startOfDay();

          // Stop if entry date is over current date (future date)
          if ($date->gt($today)) {
            return $fail("Greater than today");
          }
        }],
        'vat_challan_no' => ['required', 'max:16', function ($attribute, $value, $fail) {
          // enforce pattern: first 4 digits, dash, then 10–11 digits
          if ((!empty($value) && !preg_match('/^\d{4}-\d{11}$/', $value))) return $fail('Challan must be 16 char');

          // uniqueness against stored value
          if (!empty($value)) {
            $exists = $this->creditnote->where('challan_number', $value)->exists();
            if ($exists) return $fail('Exists, change your input');
          }
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
        'vat_in_challan' => 'required|numeric|gt:0',
        'sd_in_challan' => 'required|numeric|gte:0',
        'value_of_decreasing_adjustment' => 'required|numeric|gt:0',
        'quantity_of_decreasing_adjustment' => 'required|numeric|gt:0',
        'vat_of_decreasing_adjustment' => 'required|numeric|gt:0',
        'sd_of_decreasing_adjustment' => 'required|numeric|gt:0',
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
        'vat_challan_no.required' => 'Challan number required',
        'vat_challan_no.max' => 'Max 16 char',
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
        'vat_in_challan.gt' => 'Number > 0',
        'sd_in_challan.required' => 'SD required',
        'sd_in_challan.numeric' => 'Number only',
        'sd_in_challan.gt' => 'Number > 0',
        'value_of_decreasing_adjustment.required' => 'Decrease Value required',
        'value_of_decreasing_adjustment.numeric' => 'Number only',
        'value_of_decreasing_adjustment.gt' => 'Number > 0',
        'quantity_of_decreasing_adjustment.required' => 'Decrease quantity required',
        'quantity_of_decreasing_adjustment.numeric' => 'Number only',
        'quantity_of_decreasing_adjustment.gt' => 'Number > 0',
        'vat_of_decreasing_adjustment.required' => 'Decrease VAT required',
        'vat_of_decreasing_adjustment.numeric' => 'Number only',
        'vat_of_decreasing_adjustment.gt' => 'Number > 0',
        'sd_of_decreasing_adjustment.required' => 'Decrease SD required',
        'sd_of_decreasing_adjustment.numeric' => 'Number only',
        'sd_of_decreasing_adjustment.gt' => 'Number > 0',
        'reason_for_issuance.required' => 'Reason required',
        'reason_for_issuance.max' => 'Max 150 char',
        'remarks.max' => 'Max 250 char'
      ]
    );

    try {
      DB::transaction(function () use ($formField, $request) {
        $this->creditnote->create($formField);
      });

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
    $isAdmin = in_array(optional($user->role)->name, ['admin']);

    $creditnote = $this->creditnote->with(['client.division', 'client.user', 'client.user.role', 'item', 'unit'])->find($id);

    if (!$isAdmin && $creditnote?->client->user_id !== $user->id) {
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
    $isAdmin = in_array(optional($user->role)->name, ['admin']);

    $formField = $request->validate(
      [
        'reason_for_issuance' => 'required|max:150',
        'lc_or_invoice_no' => 'required|max:50',
        'client_id' => ['required', 'numeric', 'gt:0', function ($attribute, $value, $fail) use ($user, $isAdmin) {
          $client = $this->client->find($value);

          if (!$client) return $fail('Client does not exist');

          if ($client->status == 0) return $fail('BIN inactive');

          if (!$isAdmin && $client->user_id !== $user->id) return $fail('Unauthorised');
        }],
        'item_id' => ['required', 'numeric', 'gt:0', function ($attribute, $value, $fail) {
          $item = $this->item->find($value);
          if (!$item) return $fail('Not exists');
        }],
        'issued_date' => ['required', 'date_format:"Y-m-d"', function ($attribute, $value, $fail) {
          $date = Carbon::parse($value, 'Asia/Dhaka')->startOfDay();
          $today = Carbon::today('Asia/Dhaka')->startOfDay();

          // Stop if entry date is over current date (future date)
          if ($date->gt($today)) {
            return $fail("Greater than today");
          }
        }],
        'vat_challan_no' => 'required|max:16',
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
        'vat_in_challan' => 'required|numeric|gt:0',
        'sd_in_challan' => 'required|numeric|gte:0',
        'value_of_decreasing_adjustment' => 'required|numeric|gt:0',
        'quantity_of_decreasing_adjustment' => 'required|numeric|gt:0',
        'vat_of_decreasing_adjustment' => 'required|numeric|gt:0',
        'sd_of_decreasing_adjustment' => 'required|numeric|gt:0',
        'remarks' => 'nullable|max:250'
      ],
      [
        'reason_for_issuance.required' => 'Reason required',
        'reason_for_issuance.max' => 'Max 150 char',
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
        'vat_challan_no.required' => 'Challan number required',
        'vat_challan_no.max' => 'Max 16 char',
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
        'vat_in_challan.gt' => 'Number > 0',
        'sd_in_challan.required' => 'SD required',
        'sd_in_challan.numeric' => 'Number only',
        'sd_in_challan.gt' => 'Number > 0',
        'value_of_decreasing_adjustment.required' => 'Decrease Value required',
        'value_of_decreasing_adjustment.numeric' => 'Number only',
        'value_of_decreasing_adjustment.gt' => 'Number > 0',
        'quantity_of_decreasing_adjustment.required' => 'Decrease quantity required',
        'quantity_of_decreasing_adjustment.numeric' => 'Number only',
        'quantity_of_decreasing_adjustment.gt' => 'Number > 0',
        'vat_of_decreasing_adjustment.required' => 'Decrease VAT required',
        'vat_of_decreasing_adjustment.numeric' => 'Number only',
        'vat_of_decreasing_adjustment.gt' => 'Number > 0',
        'sd_of_decreasing_adjustment.required' => 'Decrease SD required',
        'sd_of_decreasing_adjustment.numeric' => 'Number only',
        'sd_of_decreasing_adjustment.gt' => 'Number > 0',
        'remarks.max' => 'Max 250 char'
      ]
    );

    try {
      DB::transaction(function () use ($formField, $request, $id) {
        $this->creditnote->where('id', $id)->update($formField);
      });

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
    $isAdmin = in_array(optional($user->role)->name, ['admin']);
    $ids = explode(',', $id);

    $creditnote = $this->creditnote->with(['client'])->whereIn('id', $ids)->get();

    if ($creditnote->isEmpty()) {
      return back()->with('error', 'No data found');
    }

    if (!$isAdmin) {
      $permission = $creditnote->filter(function ($row) use ($user) {
        return $row->client?->user_id !== $user->id;
      });

      if ($permission->isNotEmpty()) {
        return back()->with('error', 'Unauthorised.');
      }
    }

    $this->creditnote->whereIn('id', $ids)->delete();
    return back()->with('message', 'Credit none deleted');
  }
}
