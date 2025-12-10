<?php

namespace Modules\Report\Http\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Modules\Client\Models\Client;
use Modules\Purchase\Models\Purchase;
use Spatie\Browsershot\Browsershot;

class BoeController extends Controller
{
  public $client;
  public $purchase;
  public function __construct(Client $client, Purchase $purchase)
  {
    $this->client = $client;
    $this->purchase = $purchase;
  }

  public function index(Request $request, $client_id, $monthYear)
  {
    $user = Auth::user();
    $isAdmin = in_array(optional($user->role)->name, ['admin']);

    $period = date('Y-m', strtotime($monthYear));

    $client = $this->client->findOrFail($client_id);

    if (!$isAdmin && $client->user_id !== $user->id) {
      abort(403, 'Unauthorised'); // <-- hard block, no data
    }

    $query = $this->purchase
      ->with(['client', 'item', 'mushaknote', 'unit', 'stationcode'])
      ->when(!$isAdmin, fn($query) => $query->whereHas('client', fn($q) => $q->where('user_id', $user->id)))
      ->where('client_id', $client_id)
      ->whereRaw('DATE_FORMAT(entry_date, "%Y-%m") = ?', [$period])
      ->orderBy('id'); // important for chunkById

    // collect all rows in chunks
    $purchase = collect();

    $query->chunkById(500, function ($rows) use (&$purchase) {
      $purchase = $purchase->concat($rows);
    });

    // ----- TOTAL BOE COUNT -----
    $totalBoe = $this->purchase->where('client_id', $client_id)->whereRaw('DATE_FORMAT(entry_date, "%Y-%m") = ?', [$period])->count('boe');

    return Inertia::render('report/boe/index', ['boe' => [
      'client' => $client,
      'purchase' => $purchase,
      'totalBoe' => $totalBoe
    ]]);
  }

  /**
   * This is for BoE pdf shows
   */
  public function pdf(Request $request)
  {
    $user = Auth::user();
    $isAdmin = in_array(optional($user->role)->name, ['admin']);
    $client_id = $request->query('client_id');
    $monthYear = $request->query('monthYear');

    $period = date('Y-m', strtotime($monthYear));

    $client = $this->client->findOrFail($client_id);

    if (!$isAdmin && $client->user_id !== $user->id) {
      abort(403, 'Unauthorised'); // <-- hard block, no data
    }

    $query = $this->purchase
      ->with(['client', 'item', 'mushaknote', 'unit', 'stationcode'])
      ->when(!$isAdmin, fn($query) => $query->whereHas('client', fn($q) => $q->where('user_id', $user->id)))
      ->where('client_id', $client_id)
      ->whereRaw('DATE_FORMAT(entry_date, "%Y-%m") = ?', [$period])
      ->orderBy('id'); // important for chunkById

    // collect all rows in chunks
    $purchase = collect();

    $query->chunkById(500, function ($rows) use (&$purchase) {
      $purchase = $purchase->concat($rows);
    });

    // ----- TOTAL BOE COUNT -----
    $totalBoe = $this->purchase->where('client_id', $client_id)->whereRaw('DATE_FORMAT(entry_date, "%Y-%m") = ?', [$period])->count('boe');

    $html = view('pdf.boe', [
      'boe' => [
        'client' => $client,
        'purchase' => $purchase,
        'totalBoe' => $totalBoe
      ]
    ])->render();

    $browser = Browsershot::html($html)
      ->showBackground()
      ->margins(10, 10, 10, 10)
      ->format('A4')
      ->emulateMedia('print')
      ->landscape()
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
    $fileName = "filename=BoE-{$client->bin}" . Carbon::parse($monthYear)->format('M-Y');

    return response($pdfData)
      ->header('Content-Type', 'application/pdf')
      ->header('Content-Disposition', "inline; $fileName");
  }
}
