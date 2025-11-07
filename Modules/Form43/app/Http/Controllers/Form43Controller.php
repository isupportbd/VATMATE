<?php

namespace Modules\Form43\Http\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Client\Models\Client;
use Modules\Form43\Models\Form43;
use Modules\Item\Models\Item;
use Modules\Unit\Models\Unit;

class Form43Controller extends Controller
{
  public $client;
  public $criteria;
  public $form43;
  public $item;
  public $unit;
  public function __construct(Client $client, Form43 $form43, Item $item, Unit $unit)
  {
    $this->client = $client;
    $this->form43 = $form43;
    $this->item = $item;
    $this->unit = $unit;
  }

  // function call automatically from form43 page
  public function index(Request $request)
  {
    $user   = Auth::user();
    $isAdmin = in_array($user->role->name, ['admin']);
    $search = trim((string) $request->input('search', ''));

    $form43 = $this->form43->select('id', 'client_id', 'item_id', 'last')->with(['client', 'item', 'additionsum'])
      ->when($search, function ($query) use ($search, $user, $isAdmin) {
        $query->where(function ($query) use ($search, $user, $isAdmin) {
          $query->whereHas('client', function ($query) use ($search, $user, $isAdmin) {
            $query->when(!$isAdmin, fn($q) => $q->where('user_id', $user->id));
            $query->where(function ($query) use ($search) {
              $query->where('company_name', 'like', "%{$search}%")
                ->orWhere('bin', 'like', "%{$search}%")
                ->orWhere('mobile', 'like', "%{$search}%")
                ->orWhereHas('user', fn($query) => $query->where('email', 'like', "%{$search}%"));
            });
          })->orWhereHas('item', function ($query) use ($search) {
            $query->where('name', 'like', "%{$search}%")
              ->orWhere('hs_code', 'like', "%{$search}%");
          });
        });
      });

    if (!$isAdmin) {
      $form43->whereHas('client', fn($query) => $query->where('user_id', $user->id))->where('last', 1);
    }

    $result = $form43->orderBy('id', 'desc')->paginate((int)request('size', 10))->withQueryString();

    return Inertia("form43/index", [
      'form43' => $result,
      'search' => $search
    ]);
  }

  // // function call from form43 page
  // public function form43ClientItemCriteriaImport()
  // {
  //   $user = Auth::user();

  //   $form43 = $this->form43->select('id', 'clientbin_id', 'unit_id', 'wastage', 'unit_price')->with(['clientbin:id,client_id', 'clientbin.client:id,user_id', 'unit:id,name']);

  //   if ($user->role->name !== 'admin') {
  //     $form43->whereHas('clientbin.client', fn($query) => $query->where('user_id', $user->id));
  //   }

  //   // criteria id found for import
  //   $criteria = $this->criteria->where('name', 'import')->value('id');
  //   // last item found
  //   $result = $form43->where([['clientbin_id', request('clientbin_id')], ['item_id', request('item_id')], ['criteria_id', $criteria], ['last', 1]])->first();

  //   $msg = $result ? ['form43' => $result] : ['msg' => 'Nothing found'];
  //   return response()->json($msg);
  // }

  // // function call from form43 page
  // public function form43ClientItemCriteriaLocal()
  // {
  //   $user = Auth::user();

  //   $form43 = $this->form43->select('id', 'clientbin_id', 'unit_id', 'wastage', 'unit_price')->with(['clientbin:id,client_id', 'clientbin.client:id,user_id', 'unit:id,name']);

  //   if ($user->role->name !== 'admin') {
  //     $form43->whereHas('clientbin.client', fn($query) => $query->where('user_id', $user->id));
  //   }

  //   // criteria id found for local
  //   $criteria = $this->criteria->where('name', 'local')->value('id');
  //   // last item found
  //   $result = $form43->where([['clientbin_id', request('clientbin_id')], ['item_id', request('item_id')], ['criteria_id', $criteria], ['last', 1]])->first();

  //   $msg = $result ? ['form43' => $result] : ['msg' => 'Nothing found'];
  //   return response()->json($msg);
  // }

  // function call from form43 page
  public function form43ClientItemLast(Request $request)
  {
    $user = Auth::user();
    $isAdmin = in_array($user->role->name, ['admin']);

    $form43 = $this->form43->select('id', 'client_id', 'purchase_unit_id', 'sale_unit_id', 'wastage', 'unit_price', 'item_code', 'last', 'factor')->with(['client:id,user_id', 'purchaseunit:id,name', 'saleunit:id,name', 'additionsum']);

    if (!$isAdmin) {
      $form43->whereHas('client', fn($query) => $query->where('user_id', $user->id));
    }

    // last item found
    $result = $form43->where([['client_id', $request->client_id], ['item_id', $request->item_id], ['last', 1]])->first();

    $msg = $result ? ['lastform43' => $result] : ['msg' => 'Nothing found'];
    return response()->json($msg);
  }

  // function call from statiocode form43 for create
  public function store(Request $request)
  {
    $user = Auth::user();
    $isAdmin = in_array($user->role->name, ['admin']);

    $formField = $request->validate(
      [
        'lc_no' => ['required', 'max:50', function ($attribute, $value, $fail) use ($request) {
          $exists = $this->form43->where([['lc_no', $value], ['client_id', $request->client_id], ['item_id', $request->item_id]])->exists();

          if ($exists) {
            $fail('Exists, change your input');
          }
        }],
        'client_id' => ['required', 'numeric', 'gt:0', function ($attribute, $value, $fail) use ($user, $isAdmin) {
          if (!$isAdmin) {
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
        'purchase_unit_id' => ['required', 'numeric', 'gt:0', function ($attribute, $value, $fail) {
          // check exists or not
          $unitCheck = $this->unit->where('id', $value)->exists();
          !$unitCheck && $fail('Not exists');
        }],
        'sale_unit_id' => ['required', 'numeric', 'gt:0', function ($attribute, $value, $fail) {
          // check exists or not
          $unitCheck = $this->unit->where('id', $value)->exists();
          !$unitCheck && $fail('Not exists');
        }],
        'factor' => 'required|numeric|gt:0',
        'item_code' => [
          'required',
          'numeric',
          'gt:0',
          // function ($attribute, $value, $fail) use ($request) {
          //   $form43 = $this->form43->where(['client_id' => $request->client_id, 'item_id' => $request->item_id])->latest('id')->first();
          //   if ($form43 && ($form43->item_code == $value)) {
          //     $fail(($form43->item_code == $value));
          //   }
          // }
        ],
        'submission_date' => ['required', 'date_format:"Y-m-d"', function ($attribute, $value, $fail) {
          $inputdate = Carbon::parse($value)->format('Y-m-d');
          $currentdate = Carbon::now('Asia/Dhaka')->toDateString();

          ($inputdate !== $currentdate) && $fail("Select current date");
        }],
        'first_delivery_date' => 'nullable|date_format:"Y-m-d"',
        'boe_date' => 'required|date_format:"Y-m-d"',
        'boe_no' => 'required|max:20',
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

      return back()->with('message', 'Form 4.3 created');
    } catch (Exception $e) {
      return back()->with("error", $e->getMessage());
    }
  }

  // function call from form43 page as api for same page
  public function show($id)
  {
    $user = Auth::user();
    $isAdmin = in_array($user->role->name, ['admin']);

    $form43 = $this->form43->with(['client', 'client.division', 'client.user', 'client.user.role', 'item', 'purchaseunit', 'saleunit', 'addition', 'addition.costsheet', 'additionsum']);

    if (!$isAdmin) {
      $form43->whereHas('client', fn($query) => $query->where('user_id', $user->id));
    }

    $result = $form43->find($id);
    $msg = $result ? ['form43' => $result] : ['msg' => 'Nothing found'];
    return response()->json($msg);
  }

  // function call form form43 page for update
  public function update(Request $request, $id)
  {
    $user = Auth::user();
    $isAdmin = in_array($user->role->name, ['admin']);

    $formField = $request->validate(
      [
        'lc_no' => 'required|max:50',
        'client_id' => ['required', 'numeric', 'gt:0', function ($attribute, $value, $fail) use ($user, $isAdmin) {
          if (!$isAdmin) {
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
        'purchase_unit_id' => ['required', 'numeric', 'gt:0', function ($attribute, $value, $fail) {
          // check exists or not
          $unitCheck = $this->unit->where('id', $value)->exists();
          !$unitCheck && $fail('Not exists');
        }],
        'sale_unit_id' => ['required', 'numeric', 'gt:0', function ($attribute, $value, $fail) {
          // check exists or not
          $unitCheck = $this->unit->where('id', $value)->exists();
          !$unitCheck && $fail('Not exists');
        }],
        'factor' => 'required|numeric|gt:0',
        'item_code' => [
          'required',
          'numeric',
          'gt:0',
          // function ($attribute, $value, $fail) use ($request) {
          //   $form43 = $this->form43->where(['client_id' => $request->client_id, 'item_id' => $request->item_id])->latest('id')->first();
          //   if ($form43 && ($form43->item_code == $value)) {
          //     $fail(($form43->item_code == $value));
          //   }
          // }
        ],
        'submission_date' => ['required', 'date_format:"Y-m-d"', function ($attribute, $value, $fail) {
          $inputdate = Carbon::parse($value)->format('Y-m-d');
          $currentdate = Carbon::now()->format('Y-m-d');

          ($inputdate !== $currentdate) && $fail("Select current date");
        }],
        'first_delivery_date' => 'nullable|date_format:"Y-m-d"',
        'boe_date' => 'required|date_format:"Y-m-d"',
        'boe_no' => 'required|max:20',
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

      return back()->with('message', 'Form 4.3 updated');
    } catch (Exception $e) {
      return back()->with("error", $e->getMessage());
    }
  }

  // function call from form43 page for delete
  public function destroy($id)
  {
    $user = Auth::user();
    $isAdmin = in_array($user->role->name, ['admin']);

    try {
      DB::transaction(function () use ($id, $user, $isAdmin) {
        $form43 = $this->form43->with(['client'])->where('id', $id);

        if (!$isAdmin) {
          $form43->whereHas('client', fn($query) => $query->where('user_id', $user->id));
        }

        // Fetch records before delete to check ownership
        $toDelete = $form43->get();
        // If no record belongs to user → block deletion
        if ($toDelete->isEmpty()) {
          return back()->with('error', 'Not your client data');
        }

        // find out current id form43
        $form43ById = $this->form43->where('id', $id)->first();
        // delete current id
        $result = $form43->delete();

        // find out last form43 with form43ById
        $last = $this->form43->where([['client_id', $form43ById->client_id], ['item_id', $form43ById->item_id]])->groupBy('client_id', 'item_id')->max('id');
        $form43Last = $this->form43->where('id', $last)->first();
        // in this case we will delete this id than update previous last 1
        !empty($form43Last) && $form43Last->where('id', $last)->update(['last' => 1]);
      });

      return back()->with('message', 'Form 4.3 deleted');
    } catch (Exception $e) {
      return back()->with("error", $e->getMessage());
    }
  }
}
