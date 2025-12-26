<?php

namespace Modules\Report\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Traits\ChecksUserRole;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Client\Models\Client;
use Modules\Creditnote\Models\Creditnote;
use Modules\Sale\Models\Sale;
use Spatie\Browsershot\Browsershot;

class Report67Controller extends Controller
{
  use ChecksUserRole;

  public $client;
  public $creditnote;
  public $sale;
  public function __construct(Client $client, Creditnote $creditnote, Sale $sale)
  {
    $this->client = $client;
    $this->creditnote = $creditnote;
    $this->sale = $sale;
  }

  public function index(Request $request, $client_id, $monthYear)
  {
    $user   = Auth::user();
    $search = trim((string) $request->input('search', ''));
    $termLike   = $search !== '' ? "%$search%" : null;

    $client = $this->client->findOrFail($client_id);

    if (!$this->hasRole(['admin']) && $client->user_id !== $user->id) {
      abort(403, 'Unauthorised'); // <-- hard block, no data
    }

    $query = $this->creditnote->with(['client.division', 'client.user', 'client.user.role', 'item', 'unit'])
      ->where('client_id', $client_id)
      ->where(DB::raw("TO_CHAR(issued_date, 'YYYY-MM')"), $monthYear)
      ->when(!$this->hasRole(['admin']), fn($query) => $query->whereHas('client', fn($q) => $q->where('user_id', $user->id)))
      ->when($search !== '', function ($query) use ($termLike) {
        $query->where(function ($q) use ($termLike) {
          $q->where(DB::raw("TO_CHAR(issued_date, 'YYYY-MM')"), 'ilike', $termLike)
            ->orWhereHas('item', function ($query) use ($termLike) {
              $query->where('name', 'ilike', $termLike)->orWhere('hs_code', 'ilike', $termLike);
            });
        });
      })->orderBy('id', 'desc')
      ->paginate((int)request('size', 10))
      ->withQueryString();

    return Inertia("report/report67/index", [
      'creditnote' => $query,
      'search' => $search
    ]);
  }

  public function show($id)
  {
    $user    = Auth::user();

    $creditnote = $this->creditnote->with(['client.division', 'client.user', 'client.user.role', 'item', 'unit'])->find($id);

    if (!$this->hasRole(['admin']) && ($creditnote->client?->user_id !== $user->id)) {
      return back()->with('error', 'Unauthorised');
    }

    $customer = $this->sale->where('client_id', $creditnote->client_id)->where("invoice_no", $creditnote->lc_or_invoice_no)->first();

    return Inertia("report/report67/View", ['view' => $creditnote, 'customer' => $customer]);
  }

  /**
   * This is for 6.7 pdf shows
   */
  public function pdf(Request $request)
  {
    $user    = Auth::user();

    $creditnote = $this->creditnote->with(['client.division', 'client.user', 'client.user.role', 'item', 'unit'])
      ->findOrFail($request->query('id'));

    if (!$this->hasRole(['admin']) && ($creditnote->client?->user_id !== $user->id)) {
      return back()->with('error', 'Unauthorised');
    }

    $html = view('pdf.report67', [
      'view' => $creditnote,
    ])->render();

    $browser = Browsershot::html($html)
      ->showBackground()
      ->margins(10, 10, 10, 10)
      ->format('A4')
      ->emulateMedia('print')
      ->setOption('args', [
        '--no-sandbox',
        '--disable-setuid-sandbox',
        '--disable-dev-shm-usage',
      ]);

    // 👇 only uses the path when it exists (on server)
    if ($path = env('BROWSERSHOT_CHROME_PATH')) {
      $browser->setChromePath($path);
    }

    $pdfData = $browser->pdf();
    $fileName = "filename=report67-{$creditnote?->client->bin}-" . Carbon::parse($creditnote?->date_of_submmission)->format('M-Y') . '.pdf';

    return response($pdfData)
      ->header('Content-Type', 'application/pdf')
      ->header('Content-Disposition', "inline; $fileName");
  }
}
