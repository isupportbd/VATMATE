<?php

namespace Modules\Report\Http\Controllers;

use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Client\Models\Client;
use Modules\Form43\Models\Form43;
use Spatie\Browsershot\Browsershot;

class Report43Controller extends Controller
{
  public $client;
  public $form43;
  public function __construct(Client $client, Form43 $form43)
  {
    $this->client = $client;
    $this->form43 = $form43;
  }

  /**
   * This is for all pdf shows
   */
  public function index(Request $request, $client_id)
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
      ->when(!$isAdmin, fn($query) => $query->whereHas('client', fn($q) => $q->where('user_id', $user->id)))
      ->when($search, function ($query) use ($search) {
        $query->where(function ($q) use ($search) {
          $q->where(DB::raw("DATE_FORMAT(boe_date, '%Y-%m')"), 'like', "%{$search}%")
            ->orWhereHas('item', function ($query) use ($search) {
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

  /**
   * This for a single file show
   */
  public function show($id)
  {
    $user    = Auth::user();
    $isAdmin = in_array(optional($user->role)->name, ['admin']);

    $report43 = $this->form43->with(['client.division', 'client.user.role', 'item', 'purchaseunit', 'saleunit', 'additions.costsheet', 'additionsum'])->find($id);

    if (!$isAdmin && ($report43->client?->user_id !== $user->id)) {
      return back()->with('error', 'Unauthorised');
    }

    return Inertia("report/report43/View", ['view' => $report43]);
  }

  /**
   * This is a forwarding file for 4.3
   */
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

  /**
   * This is for 4.3 pdf shows
   */
  public function pdf(Request $request)
  {
    $user    = Auth::user();
    $isAdmin = in_array(optional($user->role)->name, ['admin']);

    $form43 = $this->form43->with(['client.division', 'client.user.role', 'item', 'purchaseunit', 'saleunit', 'additions.costsheet', 'additionsum'])
      ->findOrFail($request->query('id'));

    if (!$isAdmin && ($form43->client?->user_id !== $user->id)) {
      return back()->with('error', 'Unauthorised');
    }

    $html = view('pdf.report43', [
      'view' => $form43,
    ])->render();

    $browser = Browsershot::html($html)
      ->showBackground()
      ->margins(10, 10, 10, 10)
      ->windowSize(1200, 1600)      // 👈 portrait viewport (width < height)
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
    $fileName = "filename=report43-{$form43?->client->bin}-" . Carbon::parse($form43?->date_of_submmission)->format('M-Y');

    return response($pdfData)
      ->header('Content-Type', 'application/pdf')
      ->header('Content-Disposition', "inline; $fileName");
  }

  /**
   * This is for 4.3 pdf shows
   */
  public function pdfForwarding(Request $request)
  {
    $user    = Auth::user();
    $isAdmin = in_array(optional($user->role)->name, ['admin']);

    $form43 = $this->form43->with(['client.division', 'client.user.role', 'item', 'purchaseunit', 'saleunit', 'additions.costsheet', 'additionsum'])
      ->findOrFail($request->query('id'));

    if (!$isAdmin && ($form43->client?->user_id !== $user->id)) {
      return back()->with('error', 'Unauthorised');
    }

    $html = view('pdf.report43-forwarding', [
      'view' => $form43,
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
    $fileName = "filename=report43-forwarding-{$form43?->client->bin}-" . Carbon::parse($form43?->date_of_submmission)->format('M-Y');

    return response($pdfData)
      ->header('Content-Type', 'application/pdf')
      ->header('Content-Disposition', "inline; $fileName");
  }
}
