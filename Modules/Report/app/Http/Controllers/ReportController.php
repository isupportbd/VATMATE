<?php

namespace Modules\Report\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Traits\ChecksUserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Modules\Client\Models\Client;
use Modules\Form621\Models\Form621;

class ReportController extends Controller
{
  use ChecksUserRole;

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
    $search = trim((string) $request->input('search', ''));
    $termLike = $search !== '' ? trim('%' . strtolower($search) . '%') : null;

    $form621 = $this->form621->with(['client', 'client.division', 'client.user.role'])
      ->when(!$this->hasRole(['admin']), fn($query) => $query->whereHas('client', fn($q) => $q->where('user_id', $user->id)))
      ->when($search !== '', function ($query) use ($termLike) {
        $query->whereHas('client', fn($q) => $q->whereRaw('search_text ILIKE ?', [$termLike]));
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
    $search = trim((string) $request->input('search', ''));
    $termLike   = $search !== '' ? "%$search%" : null;

    $client = $this->client->findOrFail($client_id);

    if (!$this->hasRole(['admin']) && $client->user_id !== $user->id) {
      abort(403, 'Unauthorised'); // <-- hard block, no data
    }

    // 3) Now build query ONLY for this client
    $query = $this->form621
      ->with(['importable', 'client.user', 'item', 'unit'])
      ->select(
        'client_id',
        DB::raw("TO_CHAR(date, 'YYYY-MM') as tax_period")
      )
      ->where('client_id', $client->id) // fixed: direct filter by client
      ->when($search, fn($query) => $query->where(DB::raw("TO_CHAR(date, 'YYYY-MM')"), 'ilike', $termLike))
      ->orderBy('tax_period', 'desc')
      ->groupBy(['client_id', DB::raw("TO_CHAR(date, 'YYYY-MM')")])
      ->paginate((int) $request->input('size', 10))
      ->withQueryString();

    return Inertia("report/ClientDataList", [
      'clientdatalist' => $query,
      'search'         => $search,
    ]);
  }
}
