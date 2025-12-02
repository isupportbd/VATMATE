<?php

namespace Modules\Report\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Client\Models\Client;
use Modules\Creditnote\Models\Creditnote;

class CreditnoteController extends Controller
{
  public $client;
  public $creditnote;
  public function __construct(Client $client, Creditnote $creditnote)
  {
    $this->client = $client;
    $this->creditnote = $creditnote;
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

    $query = $this->creditnote->with(['client.division', 'client.user', 'client.user.role', 'item', 'unit'])
      ->where('client_id', $client_id)
      ->where(DB::raw("DATE_FORMAT(issued_date, '%Y-%m')"), $monthYear)
      ->when(!$isAdmin, fn($query) => $query->whereHas('client', fn($q) => $q->where('user_id', $user->id)))
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

    return Inertia("report/creditnote/index", [
      'creditnote' => $query,
      'search' => $search
    ]);
  }

  public function show($id)
  {
    $user    = Auth::user();
    $isAdmin = in_array(optional($user->role)->name, ['admin']);

    $creditnote = $this->creditnote->with(['client.division', 'client.user', 'client.user.role', 'item', 'unit'])->find($id);

    if (!$isAdmin && ($creditnote->client?->user_id !== $user->id)) {
      return back()->with('error', 'Unauthorised');
    }

    return Inertia("report/creditnote/View", ['view' => $creditnote]);
  }
}
