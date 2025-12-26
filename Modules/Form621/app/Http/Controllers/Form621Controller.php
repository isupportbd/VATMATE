<?php

namespace Modules\Form621\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Traits\ChecksUserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Modules\Creditnote\Models\Creditnote;
use Modules\Form43\Models\Form43;
use Modules\Form621\Models\Form621;
use Modules\Mushaknote\Models\Mushaknote;
use Modules\Purchase\Models\Purchase;

class Form621Controller extends Controller
{
  use ChecksUserRole;

  public $form621;
  public $form43;
  public $purchase;
  public $creditnote;
  public $mushaknote;
  public function __construct(Form621 $form621, Form43 $form43, Purchase $purchase, Creditnote $creditnote, Mushaknote $mushaknote)
  {
    $this->form621 = $form621;
    $this->form43 = $form43;
    $this->purchase = $purchase;
    $this->creditnote = $creditnote;
    $this->mushaknote = $mushaknote;
  }

  public function index(Request $request)
  {
    $user = Auth::user();
    $search = trim((string) $request->input('search', ''));
    $termLike = $search !== '' ? trim('%' . strtolower($search) . '%') : null;

    $form621 = $this->form621->with(['client', 'client.division', 'client.user.role'])
      ->when(!$this->hasRole(['admin']), fn($query) => $query->whereHas('client', fn($q) => $q->where('user_id', $user->id)))
      ->when($search !== '', function ($query) use ($termLike) {
        $query->whereHas('client', function ($query) use ($termLike) {
          $query->where(function ($q) use ($termLike) {
            $q->where('bin', 'ilike', $termLike)
              ->orWhere('mobile', 'ilike', $termLike)
              ->orWhere('company_name', 'ilike', $termLike)
              ->orWHereHas('user', fn($query) => $query->where('email', 'ilike', $termLike));
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

    $itemId = $request->input('item_id');

    if ($request->has('filter')) {
      $request->validate(['item_id' => 'nullable|numeric|gt:0']);
    }

    $form621 = $this->form621->with(['importable', 'item', 'unit', 'client', 'form43'])
      ->when(!$this->hasRole(['admin']), fn($query) => $query->whereHas('client', fn($q) => $q->where('user_id', $user->id)))
      ->when($itemId, fn($q) => $q->where('item_id', $itemId))
      ->where('client_id', $client_id)
      ->orderBy('id', 'desc')
      ->paginate((int)request('size', 10))
      ->withQueryString();

    $clientDetail = $this->form621->query()
      ->select('id', 'client_id')
      ->with(['client:id,bin,company_name'])
      ->where('client_id', $client_id)
      ->first();

    return Inertia("form621/Form621ClientTable", [
      'Form621ClientTable' => $form621,
      'clientdetail' => $clientDetail,
      'item_id' => request('item_id')
    ]);
  }

  // lc and invoice dropdown
  public function form621LcInvByClient(Request $request)
  {
    $clientId   = $request->input('client_id');
    $size       = (int) $request->input('size', 10);
    $filterType = $request->input('type', 'all'); // 'lc', 'inv', 'all'
    $search = trim((string) $request->input('search', ''));
    $termLike = $search !== '' ? trim('%' . strtolower($search) . '%') : null;

    $excludePrefixes = ['CRN', 'DBN', 'F44', 'F45'];

    $query = $this->form621->query()
      ->where('client_id', $clientId)
      ->when($search !== '', fn($q) => $q->whereRaw('search_text ILIKE ?', [$termLike]))
      ->where(function ($q) use ($excludePrefixes) {
        foreach ($excludePrefixes as $prefix) {
          $q->where('lc_or_invoice_no', 'not like', $prefix . '%');
        }
      });

    if ($filterType === 'lc') {
      // only LC (not INV)
      $query->where('lc_or_invoice_no', 'not like', 'INV%');
    } elseif ($filterType === 'inv') {
      // only invoice (INV)
      $query->where('lc_or_invoice_no', 'ilike', 'INV%');
    }

    $lcInvs = $query->selectRaw('MAX(id) as id, lc_or_invoice_no')
      ->groupBy('lc_or_invoice_no')
      ->orderBy('id', 'desc')
      ->paginate($size);

    $lcInvs->getCollection()->transform(function ($lcInv) use ($clientId) {
      if (str_starts_with($lcInv->lc_or_invoice_no, 'INV')) {
        $lcInv->has_boe = false;
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

  // ---------- 2 Item Dropdown by Client + LC/INV ----------
  public function form621ItemsByClientLcInv(Request $request)
  {
    $clientId = $request->input('client_id');
    $lcOrInv = $request->input('lc_or_invoice_no');
    $size     = (int) $request->input('size', 10);
    $search = trim((string) $request->input('search', ''));
    $termLike   = $search !== '' ? "%$search%" : null;

    $query = $this->form621->query()
      ->where('client_id', $clientId)
      ->where('lc_or_invoice_no', $lcOrInv)
      ->when($search !== '', fn($q) => $q->whereHas('item', fn($q) => $q->where('name', 'ilike', $termLike)))
      ->with('item')
      ->select('item_id')
      ->groupBy('item_id')
      ->orderBy('item_id', 'desc');

    $items = $query->paginate($size);

    return response()->json(['itemdropdown' => $items]);
  }

  // ---------- 3 BOE Dropdown by Client + LC + Item (LC only) ----------
  public function form621BoesByClientLcItem(Request $request)
  {
    $clientId = $request->input('client_id');
    $lcOrInv  = $request->input('lc_or_invoice_no');
    $itemId   = $request->input('item_id');
    $size     = (int) $request->input('size', 10);
    $search = trim((string) $request->input('search', ''));
    $termLike = $search !== '' ? trim('%' . strtolower($search) . '%') : null;

    if (str_starts_with($lcOrInv, 'INV')) {
      return response()->json(['boedropdown' => []]); // no BOEs for INV
    }

    $query = $this->form621->query()
      ->where('client_id', $clientId)
      ->where('lc_or_invoice_no', $lcOrInv)
      ->where('item_id', $itemId)
      ->when($search !== '', fn($q) => $q->whereRaw('search_text ILIKE ?', [$termLike]))
      ->select('id', 'boe')
      ->orderBy('id', 'desc');

    $boes = $query->paginate($size);

    return response()->json(['boedropdown' => $boes]);
  }

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

    $client = $request->input('client_id');
    $item = $request->input('item_id');

    $query = $this->form621->with(['client', 'item', 'form43', 'form43.purchaseunit', 'form43.saleunit', 'unit', 'user'])
      ->when(!$this->hasRole(['admin']), fn($query) => $query->whereHas('client', fn($q) => $q->where('user_id', $user->id)))
      ->where('client_id', $client)
      ->where('item_id', $item)
      ->where('last', 1)
      ->first();

    $lastDate = $this->form621->when(!$this->hasRole(['admin']), fn($query) => $query->whereHas('client', fn($q) => $q->where('user_id', $user->id)))
      ->where('client_id', $client)
      ->max('date');

    $itemCode = $this->form43->when(!$this->hasRole(['admin']),  fn($q) =>  $q->whereHas('client', fn($c) => $c->where('user_id', $user->id)))
      ->where('client_id', $client)
      ->where('item_id', $item)
      ->value('item_code');

    $maxItemCode = $this->form43->when(!$this->hasRole(['admin']),  fn($q) =>  $q->whereHas('client', fn($c) => $c->where('user_id', $user->id)))
      ->where('client_id', $client)
      ->max('item_code');

    return response()->json([
      'form621last' => $query,
      'lastdate' => $lastDate,
      'item_code' => $itemCode,
      'max_item_code' => $maxItemCode
    ]);
  }

  /**
   * This function for credit note section api call
   */
  public function creditnoteResponse(Request $request)
  {
    $user = Auth::user();
    $clientId = $request->input('client_id');
    $itemId = $request->input('item_id');
    $invNo = $request->input('lc_or_invoice_no');
    $search = trim((string) $request->input('search', ''));
    $termLike = $search !== '' ? trim('%' . strtolower($search) . '%') : null;

    $base621 = $this->form621->with(['sale', 'client', 'item', 'form43', 'unit', 'user'])
      ->when(!$this->hasRole(['admin']),  fn($q) =>  $q->whereHas('client', fn($c) => $c->where('user_id', $user->id)))
      ->when($search !== '', fn($q) => $q->whereRaw('search_text ILIKE ?', [$termLike]))
      ->where('client_id', $clientId);

    $form621 = (clone $base621)->where('item_id', $itemId)->where('lc_or_invoice_no', $invNo)->first();

    $form621LastStock = (clone $base621)->select('id', 'opening_stock', 'stock')->where('item_id', $itemId)->where('last', 1)->first();
    $form621LastDate = (clone $base621)->where('last', 1)->value('date');

    return response()->json(['creditnote' => $form621, 'form621laststock' => $form621LastStock, 'form621lastdate' => $form621LastDate]);
  }

  /**
   * This function for debit note section api call
   * It is also call from Form 4.4 and Form 4.5
   */
  public function debitnoteResponse(Request $request)
  {
    $user = Auth::user();
    $clientId = $request->input('client_id');
    $itemId = $request->input('item_id');
    $invNo = $request->input('lc_or_invoice_no');
    $boe = $request->input('boe');
    $search = trim((string) $request->input('search', ''));
    $termLike = $search !== '' ? trim('%' . strtolower($search) . '%') : null;

    $base621 = $this->form621->with(['purchase', 'client', 'item', 'form43', 'unit', 'user'])
      ->when(!$this->hasRole(['admin']),  fn($q) =>  $q->whereHas('client', fn($c) => $c->where('user_id', $user->id)))
      ->when($search, fn($q) => $q->whereRaw('search_text ILIKE ?', [$termLike]))
      ->where('client_id', $clientId);

    $form621 = (clone $base621)->where('item_id', $itemId)->where('lc_or_invoice_no', $invNo)->where('boe', $boe)->first();

    $form621lastStock = (clone $base621)->select('id', 'opening_stock', 'stock', 'last')->where('item_id', $itemId)->where('last', 1)->first();
    $form621lastDate = (clone $base621)->where('last', 1)->value('date');

    $note15 = $this->mushaknote->where('name_note_no', 'note_15')->value('percent');

    return response()->json(['debitnote' => $form621, 'form621laststock' => $form621lastStock, 'form621lastdate' => $form621lastDate, 'note15' => $note15]);
  }
}
