<?php

namespace Modules\Report\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Modules\Client\Models\Client;
use Modules\Form621\Models\Form621;

class ReportController extends Controller
{
  public $client;
  public $form621;
  public function __construct(Client $client, Form621 $form621)
  {
    $this->client = $client;
    $this->form621 = $form621;
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
          $query->where('bin', 'like', '%' . strtolower($search) . '%')
            ->orWhere('mobile', 'like', '%' . strtolower($search) . '%')
            ->orWhere('company_name', 'like', '%' . strtolower($search) . '%');
        });
      })
      ->select('client_id')
      ->groupBy(['client_id'])
      ->orderBy('client_id', 'desc')
      ->paginate((int)request('size', 10))
      ->withQueryString();

    return Inertia("report/index", ['reportclientlist' => $form621, 'search' => $search]);
  }

  public function clientDataList(Request $request, $client_id)
  {
    $user    = Auth::user();
    $isAdmin = in_array(optional($user->role)->name, ['admin']);
    $search  = $request->input('search');

    $client = $this->client->findOrFail($client_id);

    if (!$isAdmin && $client->user_id !== $user->id) {
      abort(403, 'Unauthorised'); // <-- hard block, no data
    }

    // 3) Now build query ONLY for this client
    $query = $this->form621
      ->with(['importable', 'client.user', 'item', 'unit'])
      ->select(
        'id',
        'importable_id',
        'client_id',
        'item_id',
        'unit_id',
        DB::raw("DATE_FORMAT(date, '%Y-%m') as tax_period")
      )
      ->where('client_id', $client->id) // fixed: direct filter by client
      ->when($search, function ($query) use ($search) {
        $query->whereHas('client', function ($q) use ($search) {
          $q->where(function ($q) use ($search) {
            $q->where('bin', 'like', "%{$search}%")
              ->orWhere('mobile', 'like', "%{$search}%")
              ->orWhere('company_name', 'like', "%{$search}%");
          });
        });
      })
      ->orderBy('tax_period', 'desc')
      ->groupBy('client_id', DB::raw("DATE_FORMAT(date, '%Y-%m')"))
      ->paginate((int) $request->input('size', 10))
      ->withQueryString();

    return Inertia("report/ClientDataList", [
      'clientdatalist' => $query,
      'search'         => $search,
    ]);
  }
}
