<?php

namespace Modules\Form621\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Modules\Form43\Models\Form43;
use Modules\Form621\Models\Form621;
use Modules\Purchase\Models\Purchase;

class Form621Controller extends Controller
{
  public $form621;
  public $form43;
  public $purchase;
  public function __construct(Form621 $form621, Form43 $form43, Purchase $purchase)
  {
    $this->form621 = $form621;
    $this->form43 = $form43;
    $this->purchase = $purchase;
  }

  public function index(Request $request)
  {
    $user = Auth::user();
    $isAdmin = in_array(optional($user->role)->name, ['admin']);
    $search = $request->input('search');

    $form621 = $this->form621->with(['client', 'client.division', 'client.user.role'])
      ->when(!$isAdmin, fn($query) => $query->whereHas('client', fn($q) => $q->where('user_id', $user->id)))
      ->when($search, function ($query) use ($search) {
        $query->whereHas('client', function ($query) use ($search) {
          $query->where(function ($q) use ($search) {
            $q->where('bin', 'like', '%' . strtolower($search) . '%')
              ->orWhere('mobile', 'like', '%' . strtolower($search) . '%')
              ->orWhere('company_name', 'like', '%' . strtolower($search) . '%')
              ->orWHereHas('user', fn($query) => $query->where('email', 'like', '%' . strtolower($search) . '%'));
          });
        });
      })
      ->select('client_id')
      ->groupBy(['client_id'])
      ->orderBy('client_id', 'desc')
      ->paginate((int)request('size', 10))
      ->withQueryString();

    return Inertia("form621/index", ['form621' => $form621, 'search' => $search]);
  }

  /**
   * It is created for a individual client
   * data show. it is genuinly 6.2.1
   */
  public function clientSingleIndex(Request $request,  $client_id)
  {
    $user = Auth::user();
    $isAdmin = in_array(optional($user->role)->name, ['admin']);

    if ($request->has('filter')) {
      $request->validate(['item_id' => 'nullable|numeric|gt:0']);
    }

    $form621 = $this->form621->with(['importable', 'item:id,name,hs_code', 'importable.unit:id,name', 'client:id,bin,user_id'])
      ->when(!$isAdmin, fn($query) => $query->whereHas('client', fn($q) => $q->where('user_id', $user->id)))
      ->when($request->input('item_id'), fn($q) => $q->where([
        ['client_id', $client_id],
        ['item_id', $request->input('item_id')]
      ]));

    $result = $form621->where('client_id', $client_id)->orderBy('id', 'desc')->paginate((int)request('size', 10))->withQueryString();

    $clientDetail = $this->form621->select('id', 'client_id')->with(['client:id,bin,company_name'])->where('client_id', $client_id)->first();

    return Inertia("form621/Form621ClientTable", [
      'Form621ClientTable' => $result,
      'clientdetail' => $clientDetail,
      'item_id' => request('item_id')
    ]);
  }

  // // function call from form621 page
  // public function clientDistinctIndex(Request $request)
  // {
  //   $user = Auth::user();
  //   $isAdmin = in_array(optional($user->role)->name, ['admin']);
  //   $search = $request->input('search');

  //   $form621 = $this->form621->select('id', 'client_id', 'item_id')->with(['client:id,bin,company_name,email,mobile,user_id', 'client.user:id,role_id,email,mobile', 'item:id,name,hs_code'])
  //     ->when($search, function ($query) use ($search, $user, $isAdmin) {
  //       $query->where(function ($query) use ($search, $user, $isAdmin) {
  //         $query->whereHas('client', function ($query) use ($search, $user, $isAdmin) {
  //           $query->when(!$isAdmin, fn($q) => $q->where('user_id', $user->id));
  //           $query->where('email', 'like', '%' . strtolower($search) . '%')
  //             ->orWhere('mobile', 'like', '%' . strtolower($search) . '%')
  //             ->orWhere('company_name', 'like', '%' . strtolower($search) . '%')
  //             ->orWhere('bin', 'like', '%' . strtolower($search) . '%')
  //             ->orWHereHas('user', fn($query) => $query->where('email', 'like', '%' . strtolower($search) . '%'));
  //         })->orWHereHas('item', fn($query) => $query->where('name', 'like', '%' . strtolower($search) . '%'));
  //       });
  //     });

  //   if (!$isAdmin) {
  //     $form621->whereHas('client', fn($query) => $query->where('user_id', $user->id));
  //   }

  //   $result = $form621->orderBy('id', 'desc')->groupby(['client_id'])->paginate((int)request('size', 10))->withQueryString();

  //   return Inertia("form621/index", ['form621' => $result, 'search' => $search]);
  // }

  // // function call from import 621 page
  // public function indexDistinctItem()
  // {
  //   $user = Auth::user();

  //   $import621 = $this->import621->select('id', 'clientbin_id', 'item_id')->with(['clientbin:id,bin,client_id', 'clientbin.client:id,user_id', 'item:id,name,hs_code'])
  //     ->when(request('search'), function ($query, $search) {
  //       $query->orWHereHas('item', fn($query) => $query->where('name', 'like', '%' . strtolower($search) . '%'));
  //     });

  //   if ($user->role->name !== 'admin') {
  //     $import621->whereHas('clientbin.client', fn($query) => $query->where('user_id', $user->id));
  //   }

  //   $result = $import621->where('clientbin_id', request('clientbin_id'))->orderBy('id', 'desc')->groupBy(['clientbin_id', 'item_id'])->paginate((int)request('size') ? (int)request('size') : 10);

  //   return response()->json(['import621' => $result]);
  // }

  // // function call from import 621
  // public function indexClientLastDate()
  // {
  //   $user = Auth::user();
  //   // last date import621 with clientbin_id
  //   $import621LastDate = $this->import621->where('clientbin_id', request('clientbin_id'))->groupBy(['clientbin_id'])->max('date');
  //   $import621 = $this->import621->select('id', 'clientbin_id', 'date')->with(['clientbin:id,bin,client_id', 'clientbin.client:id,user_id']);

  //   if ($user->role->name !== 'admin') {
  //     $import621->whereHas('clientbin.client', fn($query) => $query->where('user_id', $user->id));
  //   }

  //   $result = $import621->where('date', $import621LastDate)->first();

  //   return response()->json(['import621' => $result]);
  // }

  // // function call from import 621 page
  // public function indexClientItemLastStock()
  // {
  //   $user = Auth::user();
  //   $import621 = $this->import621->select('id', 'clientbin_id', 'stock')->with(['clientbin:id,client_id', 'clientbin.client:id,user_id']);

  //   if ($user->role->name !== 'admin') {
  //     $import621->whereHas('clientbin.client', fn($query) => $query->where('user_id', $user->id));
  //   }

  //   $result = $import621->where([['clientbin_id', request('clientbin_id')], ['item_id', request('item_id')], ['last', 1]])->orderBy('id', 'desc')->first();

  //   return response()->json(['import621' => $result]);
  // }

  // // function call from import 621 page
  // public function indexClientItemSecondLast()
  // {
  //   $user = Auth::user();

  //   $import621 = $this->import621->select('id', 'clientbin_id', 'stock')->with(['clientbin:id,client_id,bin', 'clientbin.client:id,user_id']);

  //   if ($user->role->name !== 'admin') {
  //     $import621->whereHas('clientbin.client', fn($query) => $query->where('user_id', $user->id));
  //   }

  //   $result = $import621->where('clientbin_id', request('clientbin_id'))->where('item_id', request('item_id'))->orderBy('id', 'desc')->skip(1)->take(1)->first();

  //   return response()->json(['import621' => $result]);
  // }

  // // function call from report import 621 page
  // public function indexDistinctClientReportImport621(Request $request)
  // {
  //   $user = Auth::user();
  //   $search = $request->input('search');

  //   $import621 = $this->import621->select('id', 'clientbin_id', 'item_id')->with(['clientbin:id,bin,client_id', 'clientbin.client:id,company_name,email,mobile,user_id', 'clientbin.client.user:id,role_id,email,mobile', 'item:id,name,hs_code'])
  //     ->when($search, function ($query) use ($search, $user) {
  //       $query->whereHas('clientbin.client', function ($query) use ($search, $user) {
  //         if (!in_array($user->role->name, ['admin'])) {
  //           $query->where('user_id', $user->id)->where(function ($query) use ($search) {
  //             $query->where('email', 'like', '%' . strtolower($search) . '%')
  //               ->orWhere('mobile', 'like', '%' . strtolower($search) . '%')
  //               ->orWhere('company_name', 'like', '%' . strtolower($search) . '%')
  //               ->orWhere('reference', 'like', '%' . strtolower($search) . '%');
  //           });
  //         } else {
  //           $query->where('email', 'like', '%' . strtolower($search) . '%')
  //             ->orWhere('mobile', 'like', '%' . strtolower($search) . '%')
  //             ->orWhere('company_name', 'like', '%' . strtolower($search) . '%')
  //             ->orWhere('reference', 'like', '%' . strtolower($search) . '%');
  //         }
  //       })->orWhereHas('clientbin', fn($query) => $query->where('bin', 'like', '%' . strtolower($search) . '%'))
  //         ->orWHereHas('clientbin.client.user', fn($query) => $query->where('email', 'like', '%' . strtolower($search) . '%'))
  //         ->orWHereHas('item', fn($query) => $query->where('name', 'like', '%' . strtolower($search) . '%'));
  //     });

  //   if (!in_array($user->role->name, ['admin'])) {
  //     $import621->whereHas('clientbin.client', fn($query) => $query->where('user_id', $user->id));
  //   }

  //   $result = $import621->orderBy('id', 'desc')->groupby(['clientbin_id'])->paginate((int)request('size') ? (int)request('size') : 10)->withQueryString();

  //   return Inertia("report/import621/index", ['import621' => $result, 'search' => $search]);
  // }

  // // function call from report import621 page
  // public function indexSingleClientReportImport621(Request $request, $clientbin_id)
  // {
  //   $user = Auth::user();

  //   $import621 = $this->import621->with(['importable', 'item:id,name,hs_code', 'importable.unit:id,name', 'clientbin:id,client_id,bin', 'clientbin.client:id,user_id']);

  //   if ($user->role->name !== 'admin') {
  //     $import621->whereHas('clientbin.client', fn($query) => $query->where('user_id', $user->id));
  //   }

  //   if (request('item_id') && request('from_date') && request('to_date')) {
  //     $import621->where('item_id', request('item_id'))->whereBetween('date', [request('from_date'), request('to_date')]);
  //   } else if (request('item_id') && request('from_date')) {
  //     $import621->where('item_id', request('item_id'))->whereDate('date', request('from_date'));
  //   } else if (request('item_id') && request('to_date')) {
  //     $import621->where('item_id', request('item_id'))->whereDate('date', request('to_date'));
  //   } else if (request('from_date') && request('to_date')) {
  //     $import621->whereBetween('date', [request('from_date'), request('to_date')]);
  //   } else if (request('item_id')) {
  //     $import621->where('item_id', request('item_id'));
  //   } else if (request('from_date')) {
  //     $import621->whereDate('date', request('from_date'));
  //   } else if (request('to_date')) {
  //     $import621->whereDate('date', request('to_date'));
  //   }

  //   $result = $import621->where('clientbin_id', $clientbin_id)->orderBy('id', 'desc')->paginate((int)request('size') ? (int)request('size') : 10)->withQueryString();

  //   $import621ClientDetail = $this->import621->select('id', 'clientbin_id')->with(['clientbin:id,bin,client_id', 'clientbin.client:id,company_name'])->where('clientbin_id', $clientbin_id)->first();

  //   return Inertia("report/import621/Import621IndividualTable", [
  //     'Import621IndividualTable' => $result,
  //     'clientdetail' => $import621ClientDetail,
  //     'size' => request('size'),
  //     'item_id' => request('item_id'),
  //     'from_date' => request('from_date'),
  //     'to_date' => request('to_date')
  //   ]);
  // }

  /**
   * Find last from Form 6.2.1
   * combination will be: client, item or
   * client, item, lc_or_invoice_no or
   * client, item, lc_or_invoice_no, last
   * and last date always set client last
   * insert date
   */
  public function form621Last(Request $request)
  {
    $user = Auth::user();
    $isAdmin = in_array(optional($user->role)->name, ['admin']);

    $client = $request->input('client_id');
    $item = $request->input('item_id');

    $query = $this->form621->with(['client', 'item', 'form43', 'form43.purchaseunit', 'form43.saleunit', 'unit', 'user'])
      ->when(!$isAdmin, fn($query) => $query->whereHas('client', fn($q) => $q->where('user_id', $user->id)))
      ->where('client_id', $client)
      ->where('item_id', $item)
      ->where('last', 1)
      ->first();

    $lastDate = $this->form621->when(!$isAdmin, fn($query) => $query->whereHas('client', fn($q) => $q->where('user_id', $user->id)))
      ->where('client_id', $client)
      ->max('date');

    $itemCode = $this->form43->when(!$isAdmin,  fn($q) =>  $q->whereHas('client', fn($c) => $c->where('user_id', $user->id)))
      ->where('client_id', $client)
      ->where('item_id', $item)
      ->value('item_code');

    $maxItemCode = $this->form43->when(!$isAdmin,  fn($q) =>  $q->whereHas('client', fn($c) => $c->where('user_id', $user->id)))
      ->where('client_id', $client)
      ->max('item_code');

    return response()->json([
      'form621last' => $query,
      'lastdate' => $lastDate,
      'item_code' => $itemCode,
      'max_item_code' => $maxItemCode
    ]);
  }

  // ---------- 1️⃣ Client Dropdown ----------
  public function form621ClientDropdown(Request $request)
  {
    $user = Auth::user();
    $isAdmin = in_array(optional($user->role)->name, ['admin']);
    $search = $request->input('search', '');
    $size = (int) $request->input('size', 10);

    $clients = $this->form621->query()
      ->when(!$isAdmin, fn($q) => $q->whereHas('client', fn($q) => $q->where('user_id', $user->id)))
      ->when($search, fn($q) => $q->whereHas('client', fn($q) => $q->where('name', 'like', "%$search%")))
      ->with('client')
      ->select('client_id')
      ->groupBy('client_id')
      ->orderBy('client_id', 'desc')
      ->paginate($size);

    return response()->json(['clientdropdown' => $clients]);
  }

  // ---------- 2️⃣ LC/INV Dropdown by Client ----------
  public function form621LcInvByClient(Request $request)
  {
    $clientId   = $request->input('client_id');
    $search     = $request->input('search', '');
    $size       = (int) $request->input('size', 10);
    $filterType = $request->input('type', 'all'); // 'lc', 'inv', 'all'

    $query = $this->form621->query()
      ->where('client_id', $clientId)
      ->when($search, fn($q) => $q->where('lc_or_invoice_no', 'like', "%$search%"));

    if ($filterType === 'lc') {
      $query->whereRaw("lc_or_invoice_no NOT LIKE 'INV%'");
    } elseif ($filterType === 'inv') {
      $query->where('lc_or_invoice_no', 'like', 'INV%');
    }

    // Fetch paginated LC/INV
    $lcInvs = $query->selectRaw('MAX(id) as id, lc_or_invoice_no')
      ->groupBy('lc_or_invoice_no')
      ->orderBy('id', 'desc')
      ->paginate($size);

    // Add has_boe flag for each LC/INV
    $lcInvs->getCollection()->transform(function ($lcInv) use ($clientId) {
      if (str_starts_with($lcInv->lc_or_invoice_no, 'INV')) {
        $lcInv->has_boe = false; // INV has no BOE
      } else {
        $lcInv->has_boe = $this->form621->where('client_id', $clientId)
          ->where('lc_or_invoice_no', $lcInv->lc_or_invoice_no)
          ->whereNotNull('boe')
          ->exists();
      }
      return $lcInv;
    });

    return response()->json(['lcinvdropdown' => $lcInvs]);
  }

  // ---------- 3️⃣ Item Dropdown by Client + LC/INV ----------
  public function form621ItemsByClientLcInv(Request $request)
  {
    $clientId = $request->input('client_id');
    $lcOrInv = $request->input('lc_or_invoice_no');
    $search   = $request->input('search', '');
    $size     = (int) $request->input('size', 10);

    $query = $this->form621->query()
      ->where('client_id', $clientId)
      ->where('lc_or_invoice_no', $lcOrInv)
      ->when($search, fn($q) => $q->whereHas('item', fn($q) => $q->where('name', 'like', "%$search%")))
      ->with('item')
      ->select('item_id')
      ->groupBy('item_id')
      ->orderBy('item_id', 'desc');

    $items = $query->paginate($size);

    return response()->json(['itemdropdown' => $items]);
  }

  // ---------- 4️⃣ BOE Dropdown by Client + LC + Item (LC only) ----------
  public function form621BoesByClientLcItem(Request $request)
  {
    $clientId = $request->input('client_id');
    $lcOrInv  = $request->input('lc_or_invoice_no');
    $itemId   = $request->input('item_id');
    $search   = $request->input('search', '');
    $size     = (int) $request->input('size', 10);

    if (str_starts_with($lcOrInv, 'INV')) {
      return response()->json(['boedropdown' => []]); // no BOEs for INV
    }

    $query = $this->form621->query()
      ->where('client_id', $clientId)
      ->where('lc_or_invoice_no', $lcOrInv)
      ->where('item_id', $itemId)
      ->when($search, fn($q) => $q->where('boe', 'like', "%$search%"))
      ->select('id', 'boe')
      ->orderBy('id', 'desc');

    $boes = $query->paginate($size);

    return response()->json(['boedropdown' => $boes]);
  }
}
