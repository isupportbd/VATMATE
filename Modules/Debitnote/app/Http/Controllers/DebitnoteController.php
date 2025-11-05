<?php

namespace Modules\Debitnote\Http\Controllers;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Modules\Client\Models\Client;
use Modules\Debitnote\Models\Debitnote;
use Modules\Item\Models\Item;
use Modules\Unit\Models\Unit;

class DebitnoteController extends Controller
{
  public $client;
  public $item;
  public $unit;
  public $debitnote;
  public function __construct(Client $client, Item $item, Unit $unit, Debitnote $debitnote)
  {
    $this->client = $client;
    $this->item = $item;
    $this->unit = $unit;
    $this->debitnote = $debitnote;
  }

  /**
   * Display a listing of the resource.
   */
  public function index(Request $request)
  {
    $search = trim((string) $request->input('search', ''));
    $user   = Auth::user();

    $debitnote = $this->debitnote->with(['client', 'client.user', 'client.user.role', 'item'])
      ->when(!in_array($user->role->name, ['admin']), function ($query) use ($user) {
        $query->whereHas('client', fn($q) => $q->where('user_id', $user->id))->where('last', 1);
      })->when($search, function ($query) use ($search) {
        $query->where(function ($query) use ($search) {
          // Search within client (company_name, email, mobile, reference, bin)
          $query->whereHas('client', function ($query) use ($search) {
            $query->where(function ($query) use ($search) {
              $query->where('company_name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('mobile', 'like', "%{$search}%")
                ->orWhere('reference', 'like', "%{$search}%")
                ->orWhere('bin', 'like', "%{$search}%");
            });
          })->orWhere('lc_or_invoice_no', 'like', "%{$search}%")
            ->orWhereHas('client.user', fn($query) => $query->where('email', 'like', "%{$search}%"))
            ->orWhereHas('client.user.role', fn($query) => $query->where('name', 'like', "%{$search}%"))
            ->orWhereHas('item', function ($query) use ($search) {
              $query->where('name', 'like', "%{$search}%")->orWhere('hs_code', 'like', "%{$search}%");
            });
        });
      });

    $result = $debitnote->orderBy('id', 'desc')->paginate((int)request('size') ? (int)request('size') : 10)->withQueryString();

    return Inertia("debitnote/index", [
      'debitnote' => $result,
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
        'reason_for_issuance' => 'required|max:150',
        'lc_or_invoice_no' => 'required|max:50',
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
        'issued_date' => 'required|date_format:"Y-m-d"',
        'vat_challan_no' => 'required|max:16',
        'vat_challan_date' => 'required|date_format:"Y-m-d"',
        'value_in_challan' => 'required|numeric|gt:0',
        'quantity_in_challan' => 'required|numeric|gt:0',
        'unit_id' => ['required', 'numeric', 'gt:0', function ($attribute, $value, $fail) {
          // check exists or not
          $unitCheck = $this->unit->where('id', $value)->exists();
          !$unitCheck && $fail('Not exists');
        }],
        'vat_in_challan' => 'required|numeric|gt:0',
        'sd_in_challan' => 'required|numeric|gte:0',
        'value_of_increasing_adjustment' => 'required|numeric|gt:0',
        'quantity_of_increasing_adjustment' => 'required|numeric|gt:0',
        'vat_of_increasing_adjustment' => 'required|numeric|gt:0',
        'sd_of_increasing_adjustment' => 'required|numeric|gt:0',
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
        'value_of_increasing_adjustment.required' => 'Increase Value required',
        'value_of_increasing_adjustment.numeric' => 'Number only',
        'value_of_increasing_adjustment.gt' => 'Number > 0',
        'quantity_of_increasing_adjustment.required' => 'Increase quantity required',
        'quantity_of_increasing_adjustment.numeric' => 'Number only',
        'quantity_of_increasing_adjustment.gt' => 'Number > 0',
        'vat_of_increasing_adjustment.required' => 'Increase VAT required',
        'vat_of_increasing_adjustment.numeric' => 'Number only',
        'vat_of_increasing_adjustment.gt' => 'Number > 0',
        'sd_of_increasing_adjustment.required' => 'Increase SD required',
        'sd_of_increasing_adjustment.numeric' => 'Number only',
        'sd_of_increasing_adjustment.gt' => 'Number > 0',
        'remarks.max' => 'Max 250 char'
      ]
    );

    try {
      DB::transaction(function () use ($formField, $request) {
        $this->debitnote->create($formField);
      });

      return back()->with('message', 'Debit note created');
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
    $debitnote = $this->debitnote->with(['client', 'client.division', 'client.user', 'client.user.role', 'item', 'unit']);

    if (!in_array($user->role->name, ['admin'])) {
      $debitnote->whereHas('client', fn($query) => $query->where('user_id', $user->id));
    }

    $result = $debitnote->find($id);
    $msg = $result ? ['debitnote' => $result] : ['msg' => 'Nothing found'];
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
        'reason_for_issuance' => 'required|max:150',
        'lc_or_invoice_no' => 'required|max:50',
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
        'issued_date' => 'required|date_format:"Y-m-d"',
        'vat_challan_no' => 'required|max:16',
        'vat_challan_date' => 'required|date_format:"Y-m-d"',
        'value_in_challan' => 'required|numeric|gt:0',
        'quantity_in_challan' => 'required|numeric|gt:0',
        'unit_id' => ['required', 'numeric', 'gt:0', function ($attribute, $value, $fail) {
          // check exists or not
          $unitCheck = $this->unit->where('id', $value)->exists();
          !$unitCheck && $fail('Not exists');
        }],
        'vat_in_challan' => 'required|numeric|gt:0',
        'sd_in_challan' => 'required|numeric|gte:0',
        'value_of_increasing_adjustment' => 'required|numeric|gt:0',
        'quantity_of_increasing_adjustment' => 'required|numeric|gt:0',
        'vat_of_increasing_adjustment' => 'required|numeric|gt:0',
        'sd_of_increasing_adjustment' => 'required|numeric|gt:0',
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
        'value_of_increasing_adjustment.required' => 'Increase Value required',
        'value_of_increasing_adjustment.numeric' => 'Number only',
        'value_of_increasing_adjustment.gt' => 'Number > 0',
        'quantity_of_increasing_adjustment.required' => 'Increase quantity required',
        'quantity_of_increasing_adjustment.numeric' => 'Number only',
        'quantity_of_increasing_adjustment.gt' => 'Number > 0',
        'vat_of_increasing_adjustment.required' => 'Increase VAT required',
        'vat_of_increasing_adjustment.numeric' => 'Number only',
        'vat_of_increasing_adjustment.gt' => 'Number > 0',
        'sd_of_increasing_adjustment.required' => 'Increase SD required',
        'sd_of_increasing_adjustment.numeric' => 'Number only',
        'sd_of_increasing_adjustment.gt' => 'Number > 0',
        'remarks.max' => 'Max 250 char'
      ]
    );

    try {
      DB::transaction(function () use ($formField, $request, $id) {
        $this->debitnote->where('id', $id)->update($formField);
      });

      return back()->with('message', 'Debit note updated');
    } catch (Exception $e) {
      return back()->with("error", $e->getMessage());
    }
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy($id)
  {
    $ids = explode(',', $id);
    $this->debitnote->whereIn('id', $ids)->delete();
    return back()->with('message', 'Debit note deleted');
  }
}
