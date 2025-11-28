<?php

namespace Modules\Report\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Form43\Models\Form43;

class Report43Controller extends Controller
{
  public $form43;
  public function __construct(Form43 $form43)
  {
    $this->form43 = $form43;
  }

  public function index(Request $request, $clientId, $monthYear)
  {
    $user   = Auth::user();
    $isAdmin = in_array(optional($user->role)->name, ['admin']);
    $search = trim((string) $request->input('search', ''));

    $query = $this->form43->with(['client.division', 'client.user.role', 'item', 'purchaseunit', 'saleunit', 'additions', 'additions.costsheet', 'additionsum'])
      ->where('client_id', $clientId)
      ->where(DB::raw("DATE_FORMAT(date, '%Y-%m')"), $monthYear)
      ->when(!$isAdmin, fn($query) => $query->whereHas('client', fn($q) => $q->where('user_id', $user->id))->where('last', 1))
      ->when($search, function ($query) use ($search) {
        $query->where(function ($q) use ($search) {
          $q->whereHas('client', function ($query) use ($search) {
            $query->where(function ($q) use ($search) {
              $q->where('company_name', 'like', "%{$search}%")
                ->orWhere('bin', 'like', "%{$search}%")
                ->orWhere('mobile', 'like', "%{$search}%");
            });
          })->orWhereHas('item', function ($query) use ($search) {
            $query->where('name', 'like', "%{$search}%")->orWhere('hs_code', 'like', "%{$search}%");
          });
        });
      })->addSelect(DB::raw("
        EXISTS (
            SELECT 1
            FROM form621s f
            WHERE f.client_id = form43s.client_id
              AND f.item_id   = form43s.item_id
        ) AS has_form621
    "))->orderBy('id', 'desc')
      ->paginate((int)request('size', 10))
      ->withQueryString();

    return Inertia("report/report43/index", [
      'form43' => $query,
      'search' => $search
    ]);
  }

  public function show($id)
  {
    $user    = Auth::user();
    $isAdmin = in_array(optional($user->role)->name, ['admin']);

    $form43 = $this->form43->with(['client.division', 'client.user.role', 'item', 'purchaseunit', 'saleunit', 'additions', 'additions.costsheet', 'additionsum'])->find($id);

    if (!$isAdmin && ($form43->client?->user_id !== $user->id)) {
      return response()->json(['form43' => null, 'err' => 'Unauthorised']);
    }

    return response()->json(['form43' => $form43, 'err' => null]);
  }
}
