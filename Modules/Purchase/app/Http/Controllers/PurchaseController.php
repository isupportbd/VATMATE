<?php

namespace Modules\Purchase\Http\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Client\Models\Client;
use Modules\Form43\Models\Form43;
use Modules\Form621\Models\Form621;
use Modules\Item\Models\Item;
use Modules\Mushaknote\Models\Mushaknote;
use Modules\Purchase\Models\Purchase;
use Modules\Stationcode\Models\Stationcode;
use Modules\Unit\Models\Unit;
use Modules\Vatfactor\Models\Vatfactor;

class PurchaseController extends Controller
{
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
    $search = $request->input('search');

    $import = $this->purchase->with(['client', 'client.division', 'client.user', 'client.user.role', 'stationcode', 'item', 'mushaknote', 'unit'])
      ->when($search, function ($query) use ($search, $user) {
        $query->whereHas('client', function ($query) use ($search, $user) {
          if (!in_array($user->role->name, ['admin'])) {
            $query->where('user_id', $user->id)->where(function ($query) use ($search) {
              $query->where('email', 'like', '%' . strtolower($search) . '%')
                ->orWhere('mobile', 'like', '%' . strtolower($search) . '%')
                ->orWhere('company_name', 'like', '%' . strtolower($search) . '%')
                ->orWhere('bin', 'like', '%' . strtolower($search) . '%')
                ->orWhere('reference', 'like', '%' . strtolower($search) . '%');
            });
          } else {
            $query->where('email', 'like', '%' . strtolower($search) . '%')
              ->orWhere('mobile', 'like', '%' . strtolower($search) . '%')
              ->orWhere('company_name', 'like', '%' . strtolower($search) . '%')
              ->orWhere('bin', 'like', '%' . strtolower($search) . '%')
              ->orWhere('reference', 'like', '%' . strtolower($search) . '%');
          }
        })->orWHereHas('client.user', fn($query) => $query->where('email', 'like', '%' . strtolower($search) . '%'))
          ->orWhereHas('item', fn($query) => $query->where('name', 'like', '%' . strtolower($search) . '%'))
          ->orWhereHas('stationcode', fn($query) => $query->where('code', 'like', '%' . strtolower($search) . '%'));
      });

    if ($user->role->name !== 'admin') {
      $import->whereHas('client', fn($query) => $query->where('user_id', $user->id));
    }

    $result = $import->orderBy('id', 'desc')->paginate((int)request('size', 10))->withQueryString();

    return Inertia("purchase/index", ['purchase' => $result, 'search' => $search]);
  }

  // function call from importer purchase page for create
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
          $binStatusCheck = $this->client->where([['id', $value], ['status', '0']])->first();
          $binStatusCheck && $fail("BIN inactive");

          // check exists or not
          $binCheck = $this->client->where('id', $value)->exists();
          !$binCheck && $fail('Not exists');
        }],
        'item_id' => ['required', 'numeric', 'gt:0', 'bail', function ($attribute, $value, $fail) use ($request) {
          // criteria find out import id
          $criteria = $this->criteria->where('name', 'import')->first();
          // last form43 id find out with clientbin_id, item_id, criteria_id
          $form43 = $this->form43->where([['clientbin_id', $request->input('clientbin_id')], ['item_id', $request->input('item_id')], ['criteria_id', $criteria->id], ['last', 1]])->first();

          // error show if not match clientbin_id, item_id, criteria_id == import in form_4.3
          empty($form43) && $fail('4.3 not found');

          // check exists or not
          $itemCheck = $this->item->where('id', $value)->exists();
          !$itemCheck && $fail('Not exists');
        }],
        'entry_date' => ['required', 'bail', 'date_format:"Y-m-d"', 'bail', function ($attribute, $value, $fail) use ($request) {
          // last date import621 with clientbin_id
          $import621LastDate = $this->import621->where('clientbin_id', $request->input('clientbin_id'))->groupBy(['clientbin_id'])->max('date');
          $import621 = $this->import621->where('date', $import621LastDate)->first();

          // error show import or sale will not be insert before import621 last date for this client
          (!empty($import621) && ($import621->date > $value)) && $fail("Violate last date");
        }],
        'mushaknote_id' => ['required', 'numeric', 'gt:0', 'bail', function ($attribute, $value, $fail) use ($request) {
          // find the gap two month. if work with another field with date then use DateTime not laravel Carbon
          $start = DateTime::createFromFormat('Y-m-d', $request->input('boe_date'));
          $end = DateTime::createFromFormat('Y-m-d', $request->input('entry_date'));
          $monthGap = "";
          ($end && $start) && $monthGap .= (($end->format('m') - $start->format('m')) + (12 * ($end->format('Y') - $start->format('Y')))) + 1;

          // find note id from mushaknote
          $note = $this->mushaknote->where('name_note_no', 'note_15')->first();
          // error show if entry_date and invoice_date gap over 5 month for note_15
          (($note->id == $value) && ($monthGap > 5)) && $fail('Over 5 tax period');

          // find out note
          $criteria = $this->criteria->where('name', 'import')->first();
          $noteCheck = $this->mushaknote->where([['id', $request->input('mushaknote_id')], ['criteria_id', $criteria->id]])->first();

          // error show if not mushak note under import criteria
          empty($noteCheck) && $fail('Not for import');

          // check exists or not
          $mushakCheck = $this->mushaknote->where('id', $value)->exists();
          !$mushakCheck && $fail('Not exists');
        }],
        'boe_date' => ['required', 'date_format:"Y-m-d"', function ($attribute, $value, $fail) use ($request) {
          // boe date can not be big from entry date
          $startMonth = Carbon::parse($request->input('entry_date'));
          $endMonth = Carbon::parse($value);
          // error show if boe date is big from entry date
          ($startMonth < $endMonth) && $fail("BoE date > Entry date");
        }],
        'wastage' => 'required|numeric|gte:0|decimal:0,2|between:0,100.00',
        'unit_id' => ['required', 'numeric', 'gt:0', function ($attribute, $value, $fail) use ($request) {
          // criteria find out import id
          $criteria = $this->criteria->where('name', 'import')->first();
          // last form43 id find out with clientbin_id, item_id, criteria_id
          $form43 = $this->form43->where([['clientbin_id', $request->input('clientbin_id')], ['item_id', $request->input('item_id')], ['criteria_id', $criteria->id], ['last', 1]])->first();

          // error show if not match import_6.2.1
          (!empty($form43) && ($form43->unit_id !== $value)) && $fail("Not match");

          // check exists or not
          $unitCheck = $this->unit->where('id', $value)->exists();
          !$unitCheck && $fail('Not exists');
        }],
        'unit_price' => ['required', 'numeric', 'gt:0', 'bail', function ($attribute, $value, $fail) use ($request) {
          // criteria find out import id
          $criteria = $this->criteria->where('name', 'import')->first();
          // last form43 id find out with clientbin_id, item_id, criteria_id
          $form43 = $this->form43->where([['clientbin_id', $request->input('clientbin_id')], ['item_id', $request->input('item_id')], ['criteria_id', $criteria->id], ['last', 1]])->first();
          // CALC: ((purchase unit price - 4.3 last import unit price) / 4.3 last import unit price) * 100
          // calculate from form_4.3 to check over or less 7.5%
          $calc = !empty($form43) ? ((($value - $form43->unit_price) / $form43->unit_price) * 100) : NULL;

          // error show if gap is over or less vatfactor.
          $vatfactor = $this->vatfactor->where('criteria_id', $criteria->id)->first();

          (!empty($form43) && ($calc > $vatfactor->percent)) && $fail('Over ' . $vatfactor->percent . ' (' . number_format($calc, 2, '.', '') . '%)');
          (!empty($form43) && ($calc < - ($vatfactor->percent))) && $fail('Less ' . $vatfactor->percent . ' (' . number_format($calc, 2, '.', '') . '%)');
        }],
        'stationcode_id' => ['required', 'numeric', 'gt:0', function ($attribute, $value, $fail) use ($request) {
          $stationCheck = $this->stationcode->where('id', $value)->exists();
          !$stationCheck && $fail('Not exists');
        }],
        'quantity' => 'required|numeric|gt:0',
        'net_quantity' => 'required|numeric|gt:0',
        'assessable_value' => 'required|numeric|gt:0',
        'cd' => 'required|numeric|gte:0',
        'rd' => 'required|numeric|gte:0',
        'sd' => 'required|numeric|gte:0',
        'vat' => 'required|numeric|gte:0|between:0,15.00',
        'ait' => 'required|numeric|gte:0|between:0,5.00',
        'at' => ['required', 'numeric', 'gte:0', 'between:0,7.50', function ($attribute, $value, $fail) use ($request) {
          // find the gap two month. if work with another field with date then use DateTime not Carbon
          $start = DateTime::createFromFormat('Y-m-d', $request->input('boe_date'));
          $end = DateTime::createFromFormat('Y-m-d', $request->input('entry_date'));
          $monthGap = "";
          ($end && $start) && $monthGap .= (($end->format('m') - $start->format('m')) + (12 * ($end->format('Y') - $start->format('Y')))) + 1;

          // error show if entry_date and boe_date gap over 5 month, must set 0%
          if ($monthGap > 5 && $value > 0) {
            $fail('Set 0%, Over 5 tax period');
          }
        }],
        'boe' => ['required', 'max:20', function ($attribute, $value, $fail) use ($request) {
          $import = $this->importerpurchase->where([['boe', $value], ['item_id', $request->input('item_id')], ['boe_date', $request->input('boe_date')], ['stationcode_id', $request->input('stationcode_id')]])->exists();

          $import && $fail('Exists, change your input');
        }],
        'lc_no' => 'nullable|max:50',
        'lc_date' => 'nullable|date_format:"Y-m-d"',
        'base_value_of_vat' => 'required|numeric|gt:0',
        'seller_name' => 'nullable|max:100',
        'address' => 'nullable|max:250',
        'bin_or_nid' => 'nullable|max:30',
      ],
      [
        'clientbin_id.required' => 'BIN required',
        'clientbin_id.numeric' => 'Number only',
        'clientbin_id.gt' => 'Number >0',
        'stationcode_id.required' => 'Station code required',
        'stationcode_id.numeric' => 'Number only',
        'stationcode_id.gt' => 'Number > 0',
        'item_id.required' =>  'Item required',
        'item_id.numeric' =>  'Number only',
        'item_id.gt' =>  'Number > 0',
        'mushaknote_id.required' =>  'Mushak note required',
        'mushaknote_id.numeric' =>  'Number only',
        'mushaknote_id.gt' =>  'Number > 0',
        'unit_id.required' =>  'Unit required',
        'unit_id.numeric' =>  'Number only',
        'unit_id.gt' =>  'Number > 0',
        'entry_date.required' =>  'Entry date required',
        'entry_date.date_format' =>  'Invalid date',
        'boe.required' =>  'BoE required',
        'boe.max' =>  'Max 20 char',
        'boe_date.required' =>  'BoE date required',
        'boe_date.date_format' =>  'Invalid date',
        'lc_no.max' => 'Max 50 char',
        'lc_date.date_format' =>  'Invalid date',
        'assessable_value.required' =>  'Assessable value required',
        'assessable_value.numeric' =>  'Number only',
        'assessable_value.gt' =>  'Number > 0',
        'wastage.required' =>  'Wastage required',
        'wastage.numeric' =>  'Number only',
        'wastage.gte' =>  '(+)Number only',
        'wastage.between' =>  '0 to 100.00',
        'quantity.required' =>  'Quantity required',
        'quantity.numeric' =>  'Number only',
        'quantity.gt' =>  'Number > 0',
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
        'ait.required' =>  'AIT required',
        'ait.numeric' =>  'Number only',
        'ait.gte' =>  '(+)Number only',
        'ait.between' =>  'Range 0 to 5',
        'at.required' =>  'AT required',
        'at.numeric' =>  'Number only',
        'at.gte' =>  '(+)Number only',
        'at.between' =>  'Range 0 to 7.50',
        'base_value_of_vat.required' =>  'Base value required',
        'base_value_of_vat.numeric' =>  'Number only',
        'base_value_of_vat.gt' =>  'Number > 0',
        'unit_price.required' =>  'Unit price required',
        'unit_price.numeric' =>  'Number only',
        'unit_price.gt' =>  'Number > 0',
        'seller_name.max' => 'Max 100 char',
        'address.max' => 'Max 250 char',
        'bin_or_nid.max' => 'Max 30 char',
      ]
    );

    // use transaction for those exception
    try {
      DB::transaction(function () use ($formField, $request, $user) {
        // criteria must be import
        $criteria = $this->criteria->where('name', 'import')->first();
        $formField['criteria_id'] = $criteria->id;

        // check last invoice number check with client id and entry year for invoice number
        $last = $this->importerpurchase->where('clientbin_id', $request->input('clientbin_id'))->whereYear('entry_date', $request->input('entry_date'))->groupBy(['clientbin_id', DB::raw('YEAR(entry_date)')])->max('id');
        $import = $this->importerpurchase->where('id', $last)->first();

        // invoice number increase or start from 1
        empty($import) ? ($formField['invoice_no'] = 1) : ($formField['invoice_no'] = $import->invoice_no + 1);

        $formField['cd'] = number_format($request->input('assessable_value') * ($request->input('cd') / 100), "3", ".", "");
        $formField['rd'] = number_format($request->input('assessable_value') * ($request->input('rd') / 100), "3", ".", "");
        $formField['sd'] = number_format(($request->input('assessable_value') + ($request->input('assessable_value') * ($request->input('cd') / 100)) + ($request->input('assessable_value') * ($request->input('rd') / 100))) * ($request->input('sd') / 100), "3", ".", "");
        $formField['vat'] = number_format(
          (
            doubleval($request->input('assessable_value')) +
            (doubleval($request->input('assessable_value')) * doubleval(($request->input('cd') / 100))) +
            (doubleval($request->input('assessable_value')) * doubleval(($request->input('rd') / 100))) +
            (
              (
                doubleval($request->input('assessable_value')) +
                (doubleval($request->input('assessable_value')) * (doubleval($request->input('cd')) / 100)) +
                (doubleval($request->input('assessable_value')) * (doubleval($request->input('rd')) / 100))
              ) *
              (doubleval($request->input('sd')) / 100)
            )
          ) * (doubleval($request->input('vat')) / 100),
          "3",
          ".",
          ""
        );
        $formField['ait'] = number_format($request->input('assessable_value') * ($request->input('ait') / 100), "3", ".", "");
        $formField['at'] = number_format((
          $request->input('assessable_value') +
          ($request->input('assessable_value') * ($request->input('cd') / 100)) +
          ($request->input('assessable_value') * ($request->input('rd') / 100)) +
          (
            (
              $request->input('assessable_value') +
              ($request->input('assessable_value') * ($request->input('cd') / 100)) +
              ($request->input('assessable_value') * ($request->input('rd') / 100))
            ) * ($request->input('sd') / 100)
          )
        ) * ($request->input('at') / 100), "3", ".", "");

        // dd($formField['cd'], $formField['rd'], $formField['sd'], $formField['vat'], $formField['ait'], $formField['at']);
        // create import purchase and increase or start invoice no with entry year
        $result = $this->importerpurchase->create($formField);

        if ($result) {
          // import621 check last clientbin_id, item_id and importable_type == import
          $last = $this->import621->where([['clientbin_id', $request->input('clientbin_id')], ['item_id', $request->input('item_id')]])->groupBy('clientbin_id', 'item_id')->max('id');
          $import621 = $this->import621->where('id', $last)->first();

          // for last item labeling
          !empty($import621) && $import621->where('id', $last)->update(['last' => 0]);
          // if get clientbin_id and item_id match and has stock then it will add into stock else start new stock
          $stock = empty($import621) ? $formField['net_quantity'] : ($import621->stock + $formField['net_quantity']);
          $result->import621()->create([
            'clientbin_id' => $formField['clientbin_id'],
            'item_id' => $formField['item_id'],
            'quantity' => $formField['net_quantity'],
            'unit_id' => $formField['unit_id'],
            'stock' => $stock,
            'date' => $formField['entry_date'],
            'last' => 1,
            'userid' => $user->id
          ]);
        }
      });

      DB::commit();
      return back()->with("message", 'Import purchase created');
    } catch (Throwable $e) {
      DB::rollBack();
      return back()->with("error", 'May be network issue, not perform all the task, try again');
    }
  }

  // // function call from importer purchase page as api for same page
  // public function show($id)
  // {
  //   $user = Auth::user();

  //   $import = $this->importerpurchase->with(['clientbin', 'clientbin.client', 'clientbin.client.division', 'clientbin.client.user', 'clientbin.client.user.role', 'criteria', 'stationcode', 'item', 'mushaknote', 'unit']);

  //   if ($user->role->name !== 'admin') {
  //     $import->whereHas('clientbin.client', fn($query) => $query->where('user_id', Auth::user()->id));
  //   }

  //   $result = $import->find($id);
  //   $msg = empty($result) ? ['msg' => 'Nothing found'] : ['importerpurchase' => $result];
  //   return response()->json($msg);
  // }

  // // function call form importer purchase page for update
  // public function update(Request $request, $id)
  // {
  //   $user = Auth::user();

  //   $formField = $request->validate(
  //     [
  //       'clientbin_id' => ['required', 'numeric', 'gt:0', function ($attribute, $value, $fail) use ($user) {
  //         if (!in_array($user->role->name, ['admin'])) {
  //           $userCheck = $this->clientbin->with(['client'])->where('id', $value)->whereHas('client', fn($query) => $query->where('user_id', $user->id))->exists();
  //           // error show if role is not admin and client is not under him
  //           !$userCheck && $fail('Not your client');
  //         }

  //         // bin status check
  //         $clientbin = $this->clientbin->where('id', $value)->where('status', '0')->first();
  //         $clientbin && $fail("BIN inactive");

  //         // check exists or not
  //         $binCheck = $this->clientbin->where('id', $value)->exists();
  //         !$binCheck && $fail('Not exists');
  //       }],
  //       'item_id' => ['required', 'numeric', 'gt:0', 'bail', function ($attribute, $value, $fail) use ($request, $id) {
  //         // criteria find out import id
  //         $criteria = $this->criteria->where('name', 'import')->first();
  //         // last form43 id find out with clientbin_id, item_id, criteria_id
  //         $form43 = $this->form43->where([['clientbin_id', $request->input('clientbin_id')], ['item_id', $request->input('item_id')], ['criteria_id', $criteria->id], ['last', 1]])->first();

  //         // error show if not match clientbin_id, item_id, criteria_id == import in form_4.3
  //         empty($form43) && $fail('4.3 not found');

  //         // import621 check last clientbin_id, item_id and importable_type == import
  //         $import621 = $this->import621->where('importable_id', $id)->where('importable_type', 'import')->first();
  //         // check criteria == import and last == 1 then pass
  //         if (empty($import621)) {
  //           $fail("Not match");
  //         } else if ($import621->last == 0) {
  //           $fail("Not last one");
  //         } else if ($import621 && !($import621->clientbin_id == $request->input('clientbin_id') && $import621->item_id == $value)) {
  //           // check clientbin_id and item_id in this given id credential same than pass, else fail
  //           $fail('Not same item');
  //         }

  //         // check exists or not
  //         $itemCheck = $this->item->where('id', $value)->exists();
  //         !$itemCheck && $fail('Not exists');
  //       }],
  //       'stationcode_id' => ['required', 'numeric', 'gt:0', function ($attribute, $value, $fail) use ($request) {
  //         $stationCheck = $this->stationcode->where('id', $value)->exists();
  //         !$stationCheck && $fail('Not exists');
  //       }],
  //       'entry_date' => 'required|date_format:"Y-m-d"',
  //       'mushaknote_id' => ['required', 'numeric', 'gt:0', function ($attribute, $value, $fail) use ($request) {
  //         // find the gap two month. if work with another field with date then use DateTime not laravel Carbon
  //         $start = DateTime::createFromFormat('Y-m-d', $request->input('boe_date'));
  //         $end = DateTime::createFromFormat('Y-m-d', $request->input('entry_date'));
  //         $monthGap = "";
  //         ($end && $start) && $monthGap .= (($end->format('m') - $start->format('m')) + (12 * ($end->format('Y') - $start->format('Y')))) + 1;

  //         // find note id from mushaknote
  //         $note = $this->mushaknote->where('name_note_no', 'note_15')->first();
  //         // error show if entry_date and invoice_date gap over 5 month for note_15
  //         (($note->id == $value) && ($monthGap > 5)) && $fail('Over 5 tax period');

  //         // find out note
  //         $criteria = $this->criteria->where('name', 'import')->first();
  //         $noteCheck = $this->mushaknote->where('id', $request->input('mushaknote_id'))->where('criteria_id', $criteria->id)->first();

  //         // error show if not mushak note under import criteria
  //         empty($noteCheck) && $fail('Not for import');

  //         // check exists or not
  //         $mushakCheck = $this->mushaknote->where('id', $value)->exists();
  //         !$mushakCheck && $fail('Not exists');
  //       }],
  //       'boe_date' => 'required|date_format:"Y-m-d"',
  //       'wastage' => 'required|numeric|gte:0|decimal:0,2|between:0,100.00',
  //       'unit_id' => ['required', 'numeric', 'gt:0', function ($attribute, $value, $fail) use ($request) {
  //         // criteria find out import id
  //         $criteria = $this->criteria->where('name', 'import')->first();
  //         // last form43 id find out with clientbin_id, item_id, criteria_id
  //         $form43 = $this->form43->where([['clientbin_id', $request->input('clientbin_id')], ['item_id', $request->input('item_id')], ['criteria_id', $criteria->id], ['last', 1]])->first();

  //         // error show if not match local_6.2.1
  //         ($form43 && ($form43->unit_id !== $value)) && $fail("Not match");
  //         // check exists or not
  //         $unitCheck = $this->unit->where('id', $value)->exists();
  //         !$unitCheck && $fail('Not exists');
  //       }],
  //       'unit_price' => ['required', 'numeric', 'gt:0', 'bail', function ($attribute, $value, $fail) use ($request) {
  //         // criteria find out import id
  //         $criteria = $this->criteria->where('name', 'import')->first();
  //         // last form43 id find out with clientbin_id, item_id, criteria_id
  //         $form43 = $this->form43->where([['clientbin_id', $request->input('clientbin_id')], ['item_id', $request->input('item_id')], ['criteria_id', $criteria->id], ['last', 1]])->first();
  //         // CALC: ((purchase unit price - 4.3 last import unit price) / 4.3 last import unit price) * 100
  //         // calculate from form_4.3 to check over or less 7.5%
  //         $calc = $form43 ? ((($value - $form43->unit_price) / $form43->unit_price) * 100) : NULL;

  //         // error show if gap is over or less vatfactor.
  //         $vatfactor = $this->vatfactor->where('criteria_id', $criteria->id)->first();

  //         (!empty($form43) && ($calc > $vatfactor->percent)) && $fail('Over ' . $vatfactor->percent . ' (' . number_format($calc, 2, '.', '') . '%)');
  //         (!empty($form43) && ($calc < - ($vatfactor->percent))) && $fail('Less ' . $vatfactor->percent . ' (' . number_format($calc, 2, '.', '') . '%)');
  //       }],
  //       'quantity' => 'required|numeric|gt:0',
  //       'net_quantity' => 'required|numeric|gt:0',
  //       'assessable_value' => 'required|numeric|gt:0',
  //       'cd' => 'required|numeric|gte:0',
  //       'rd' => 'required|numeric|gte:0',
  //       'sd' => 'required|numeric|gte:0',
  //       'vat' => 'required|numeric|gte:0|between:0,15.00',
  //       'ait' => 'required|numeric|gte:0|between:0,5.00',
  //       'at' => 'required|numeric|gte:0|between:0,7.50',
  //       'boe' => 'required|max:20',
  //       'lc_no' => 'nullable|max:50',
  //       'lc_date' => 'nullable|date_format:"Y-m-d"',
  //       'base_value_of_vat' => 'required|numeric|gt:0',
  //       'seller_name' => 'nullable|max:100',
  //       'address' => 'nullable|max:250',
  //       'bin_or_nid' => 'nullable|max:30',
  //     ],
  //     [
  //       'clientbin_id.required' => 'BIN required',
  //       'clientbin_id.numeric' => 'Number only',
  //       'clientbin_id.gt' => 'Number > 0',
  //       'stationcode_id.required' => 'Station code required',
  //       'stationcode_id.numeric' => 'Number only',
  //       'stationcode_id.gt' => 'Number > 0',
  //       'item_id.required' =>  'Item required',
  //       'item_id.numeric' =>  'Number only',
  //       'item_id.gt' =>  'Number > 0',
  //       'mushaknote_id.required' =>  'Mushak note required',
  //       'mushaknote_id.numeric' =>  'Number only',
  //       'mushaknote_id.gt' =>  'Number > 0',
  //       'unit_id.required' =>  'Unit required',
  //       'unit_id.numeric' =>  'Number only',
  //       'unit_id.gt' =>  'Number > 0',
  //       'entry_date.required' =>  'Entry date required',
  //       'entry_date.date_format' =>  'Invalid date',
  //       'boe.required' =>  'BoE required',
  //       'boe.max' =>  'Max 20 char',
  //       'boe_date.required' =>  'BoE date required',
  //       'boe_date.date_format' =>  'Invalid date',
  //       'lc_no.max' => 'Max 50 char',
  //       'lc_date.date_format' =>  'Invalid date',
  //       'assessable_value.required' =>  'Assessable value required',
  //       'assessable_value.numeric' =>  'Number only',
  //       'assessable_value.gt' =>  'Number > 0',
  //       'wastage.required' =>  'Wastage required',
  //       'wastage.numeric' =>  'Number only',
  //       'wastage.gte' =>  '(+)Number only',
  //       'wastage.between' =>  '0 to 100.00',
  //       'quantity.required' =>  'Quantity required',
  //       'quantity.numeric' =>  'Number only',
  //       'quantity.gt' =>  'Number > 0',
  //       'net_quantity.required' =>  'Net quantity required',
  //       'net_quantity.numeric' =>  'Number only',
  //       'net_quantity.gt' =>  '(+)Number only',
  //       'cd.required' =>  'CD required',
  //       'cd.numeric' =>  'Number only',
  //       'cd.gte' =>  '(+)Number only',
  //       'rd.required' =>  'RD required',
  //       'rd.numeric' =>  'Number only',
  //       'rd.gte' =>  '(+)Number only',
  //       'sd.required' =>  'SD required',
  //       'sd.numeric' =>  'Number only',
  //       'sd.gte' =>  '(+)Number only',
  //       'vat.required' =>  'VAT required',
  //       'vat.numeric' =>  'Number only',
  //       'vat.gte' =>  '(+)Number only',
  //       'vat.between' =>  'Range 0 to 15',
  //       'ait.required' =>  'AIT required',
  //       'ait.numeric' =>  'Number only',
  //       'ait.gte' =>  '(+)Number only',
  //       'ait.between' =>  'Range 0 to 5',
  //       'at.required' =>  'AT required',
  //       'at.numeric' =>  'Number only',
  //       'at.gte' =>  '(+)Number only',
  //       'at.between' =>  'Range 0 to 7.50',
  //       'base_value_of_vat.required' =>  'Base value required',
  //       'base_value_of_vat.numeric' =>  'Number only',
  //       'base_value_of_vat.gt' =>  'Number > 0',
  //       'unit_price.required' =>  'Unit price required',
  //       'unit_price.numeric' =>  'Number only',
  //       'unit_price.gt' =>  'Number > 0',
  //       'seller_name.max' => 'Max 100 char',
  //       'address.max' => 'Max 250 char',
  //       'bin_or_nid.max' => 'Max 30 char',
  //     ]
  //   );

  //   try {
  //     DB::transaction(function () use ($formField, $id, $request, $user) {
  //       $formField['cd'] = number_format($request->input('assessable_value') * ($request->input('cd') / 100), "3", ".", "");
  //       $formField['rd'] = number_format($request->input('assessable_value') * ($request->input('rd') / 100), "3", ".", "");
  //       $formField['sd'] = number_format(($request->input('assessable_value') + ($request->input('assessable_value') * ($request->input('cd') / 100)) + ($request->input('assessable_value') * ($request->input('rd') / 100))) * ($request->input('sd') / 100), "3", ".", "");
  //       $formField['vat'] = number_format(
  //         (
  //           doubleval($request->input('assessable_value')) +
  //           (doubleval($request->input('assessable_value')) * doubleval(($request->input('cd') / 100))) +
  //           (doubleval($request->input('assessable_value')) * doubleval(($request->input('rd') / 100))) +
  //           (
  //             (
  //               doubleval($request->input('assessable_value')) +
  //               (doubleval($request->input('assessable_value')) * (doubleval($request->input('cd')) / 100)) +
  //               (doubleval($request->input('assessable_value')) * (doubleval($request->input('rd')) / 100))
  //             ) *
  //             (doubleval($request->input('sd')) / 100)
  //           )
  //         ) * (doubleval($request->input('vat')) / 100),
  //         "3",
  //         ".",
  //         ""
  //       );
  //       $formField['ait'] = number_format($request->input('assessable_value') * ($request->input('ait') / 100), "3", ".", "");
  //       $formField['at'] = number_format((
  //         $request->input('assessable_value') +
  //         ($request->input('assessable_value') * ($request->input('cd') / 100)) +
  //         ($request->input('assessable_value') * ($request->input('rd') / 100)) +
  //         (
  //           (
  //             $request->input('assessable_value') +
  //             ($request->input('assessable_value') * ($request->input('cd') / 100)) +
  //             ($request->input('assessable_value') * ($request->input('rd') / 100))
  //           ) * ($request->input('sd') / 100)
  //         )
  //       ) * ($request->input('at') / 100), "3", ".", "");

  //       // dd($formField['cd'], $formField['rd'], $formField['sd'], $formField['vat'], $formField['ait'], $formField['at']);

  //       $updatePurchase = $this->import621->where([['importable_id', $id], ['importable_type', 'import'], ['last', 1]])->first();
  //       $result = $this->importerpurchase->where('id', $updatePurchase->importable_id)->update([
  //         'mushaknote_id' => $formField['mushaknote_id'],
  //         'stationcode_id' => $formField['stationcode_id'],
  //         'boe' => $formField['boe'],
  //         'lc_no' => $formField['lc_no'],
  //         'lc_date' => $formField['lc_date'],
  //         'assessable_value' => $formField['assessable_value'],
  //         'wastage' => $formField['wastage'],
  //         'quantity' => $formField['quantity'],
  //         'net_quantity' => $formField['net_quantity'],
  //         'cd' => $formField['cd'],
  //         'rd' => $formField['rd'],
  //         'sd' => $formField['sd'],
  //         'vat' => $formField['vat'],
  //         'ait' => $formField['ait'],
  //         'at' => $formField['at'],
  //         'base_value_of_vat' => $formField['base_value_of_vat'],
  //         'unit_price' => $formField['unit_price'],
  //         'seller_name' => $formField['seller_name'],
  //         'address' => $formField['address'],
  //         'bin_or_nid' => $formField['bin_or_nid'],
  //       ]);

  //       if ($result) {
  //         // import621 check last clientbin_id, item_id and importable_type == import
  //         $import621SecondLast = $this->import621->where([['clientbin_id', $request->input('clientbin_id')], ['item_id', $request->input('item_id')]])->orderBy('id', 'desc')->skip(1)->take(1)->first();

  //         // if get clientbin_id and item_id match and has stock then it will add into stock else start new stock
  //         $stock = empty($import621SecondLast) ? $formField['net_quantity'] : $import621SecondLast->stock + $formField['net_quantity'];
  //         $this->import621->where('importable_id', $id)->where('importable_type', 'import')->update([
  //           'quantity' => $formField['net_quantity'],
  //           'stock' => $stock,
  //           'userid' => $user->id
  //         ]);
  //       }
  //     });

  //     DB::commit();
  //     return back()->with("message", 'Import purchase updated');
  //   } catch (Exception $e) {
  //     DB::rollBack();
  //     return back()->with("error", 'May be network issue, not perform all the task, try again');
  //   }
  // }

  // // function call from importer purchase page for delete
  // public function destroy($id)
  // {
  //   $user = Auth::user();
  //   try {
  //     DB::transaction(function () use ($id, $user) {
  //       // $ids = explode(',', $id);
  //       // $import = $this->importerpurchase->with(['client'])->whereIn('id', $ids);

  //       $import621 = $this->import621->with(['clientbin.client'])->where([['id', $id], ['importable_type', 'import'], ['last', 1]]);

  //       if (!in_array($user->role->name, ['admin'])) {
  //         $import621->whereHas('clientbin.client', fn($query) => $query->where('user_id', Auth::user()->id));
  //       }

  //       // Fetch records before delete to check ownership
  //       $toDelete = $import621->get();
  //       // If no record belongs to user → block deletion
  //       if ($toDelete->isEmpty()) {
  //         return back()->with('error', 'Not your client data');
  //       }

  //       // temporary store data in variable
  //       $storeData = $import621->first();
  //       // delete current id
  //       $this->importerpurchase->where('id', $storeData->importable_id)->delete();
  //       $import621->delete();
  //       // find out last form43 with form43ById
  //       $last = $this->import621->where([['clientbin_id', $storeData->clientbin_id], ['item_id', $storeData->item_id]])->groupBy('clientbin_id', 'item_id')->max('id');
  //       $import621Last = $this->import621->where('id', $last)->first();
  //       // in this case we will delete this id than update previous last 1
  //       $import621Last && $import621Last->where('id', $last)->update(['last' => 1]);
  //     });

  //     DB::commit();
  //     return back()->with('message', 'Import purchase deleted');
  //   } catch (Exception $e) {
  //     DB::rollBack();
  //     return back()->with("error", 'May be network issue, not perform all the task, try again');
  //   }
  // }

  // // function call from importer purchase page
  // public function importerClientItemCriteria()
  // {
  //   $user = Auth::user();

  //   $last = $this->importerpurchase->where([['clientbin_id', request('clientbin_id')], ['item_id', request('item_id')], ['criteria_id', request('criteria_id')]])->groupBy(['clientbin_id', 'item_id', 'criteria_id'])->max('id');

  //   $import = $this->importerpurchase->select('id', 'clientbin_id', 'criteria_id', 'unit_price')->with(['clientbin:id,client_id,bin', 'clientbin.client:id,user_id', 'criteria:id,name']);

  //   if (!in_array(Auth::user()->role->name, ['admin'])) {
  //     $import->whereHas('clientbin.client', fn($query) => $query->where('user_id', $user->id));
  //   }

  //   $result = $import->where([['clientbin_id', request('clientbin_id')], ['item_id', request('item_id')], ['criteria_id', request('criteria_id')], ['id', $last]])->first();

  //   $msg = $result ? ['importerpurchase' => $result] : ['msg' => 'Nothing found'];
  //   return response()->json($msg);
  // }
}
