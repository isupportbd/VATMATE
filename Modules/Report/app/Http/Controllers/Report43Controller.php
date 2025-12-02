<?php

namespace Modules\Report\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Client\Models\Client;
use Modules\Form43\Models\Form43;

class Report43Controller extends Controller
{
  public $client;
  public $form43;
  public function __construct(Client $client, Form43 $form43)
  {
    $this->client = $client;
    $this->form43 = $form43;
  }

  public function index(Request $request, $client_id, $monthYear)
  {
    $user   = Auth::user();
    $isAdmin = in_array(optional($user->role)->name, ['admin']);
    $search = trim((string) $request->input('search', ''));

    $client = $this->client->findOrFail($client_id);

    if (!$isAdmin && $client->user_id !== $user->id) {
      abort(403, 'Unauthorised'); // <-- hard block, no data
    }

    $query = $this->form43->with(['client.division', 'client.user.role', 'item', 'purchaseunit', 'saleunit', 'additions.costsheet', 'additionsum'])
      ->where('client_id', $client_id)
      ->where(DB::raw("DATE_FORMAT(submission_date, '%Y-%m')"), $monthYear)
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
      })->orderBy('id', 'desc')
      ->paginate((int)request('size', 10))
      ->withQueryString();

    return Inertia("report/report43/index", [
      'report43' => $query,
      'search' => $search
    ]);
  }

  public function show($id)
  {
    $user    = Auth::user();
    $isAdmin = in_array(optional($user->role)->name, ['admin']);

    $report43 = $this->form43->with(['client.division', 'client.user.role', 'item', 'purchaseunit', 'saleunit', 'additions.costsheet', 'additionsum'])->find($id);

    if (!$isAdmin && ($report43->client?->user_id !== $user->id)) {
      return back()->with('error', 'Unauthorised');
    }

    return Inertia("report/report43/View", ['report43' => $report43]);
  }

  public function forwarding($id)
  {
    $user    = Auth::user();
    $isAdmin = in_array(optional($user->role)->name, ['admin']);

    $report43 = $this->form43->with(['client.division', 'client.user.role', 'item', 'purchaseunit', 'saleunit', 'additions.costsheet', 'additionsum'])->find($id);

    if (!$isAdmin && ($report43->client?->user_id !== $user->id)) {
      return back()->with('error', 'Unauthorised');
    }

    return Inertia("report/report43/Forwarding", ['report43' => $report43]);
  }
}
