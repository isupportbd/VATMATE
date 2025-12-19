<?php

namespace Modules\Report\Http\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Client\Models\Client;
use Modules\Debitnote\Models\Debitnote;
use Spatie\Browsershot\Browsershot;

class Report68Controller extends Controller
{
  public $client;
  public $debitnote;
  public function __construct(Client $client, Debitnote $debitnote)
  {
    $this->client = $client;
    $this->debitnote = $debitnote;
  }

  public function index(Request $request, $client_id, $monthYear)
  {
    $user   = Auth::user();
    $isAdmin = in_array(optional($user->role)->name, ['admin']);
    $search = trim((string) $request->input('search', ''));
    $termLike   = $search !== '' ? $search . '%' : null;

    $client = $this->client->findOrFail($client_id);

    if (!$isAdmin && $client->user_id !== $user->id) {
      abort(403, 'Unauthorised'); // <-- hard block, no data
    }

    $query = $this->debitnote->with(['client.division', 'client.user', 'client.user.role', 'item', 'unit'])
      ->where('client_id', $client_id)
      ->where(DB::raw("DATE_FORMAT(issued_date, '%Y-%m')"), $monthYear)
      ->when(!$isAdmin, fn($query) => $query->whereHas('client', fn($q) => $q->where('user_id', $user->id)))
      ->when($search, function ($query) use ($termLike) {
        $query->where(function ($q) use ($termLike) {
          $q->where(DB::raw("DATE_FORMAT(issued_date, '%Y-%m')"), 'like', $termLike)
            ->orWhereHas('item', function ($query) use ($termLike) {
              $query->where('name', 'like', $termLike)->orWhere('hs_code', 'like', $termLike);
            });
        });
      })->orderBy('id', 'desc')
      ->paginate((int)request('size', 10))
      ->withQueryString();

    return Inertia("report/report68/index", [
      'debitnote' => $query,
      'search' => $search
    ]);
  }

  public function show($id)
  {
    $user    = Auth::user();
    $isAdmin = in_array(optional($user->role)->name, ['admin']);

    $debitnote = $this->debitnote->with(['client.division', 'client.user', 'client.user.role', 'item', 'unit'])->find($id);

    if (!$isAdmin && ($debitnote->client?->user_id !== $user->id)) {
      return back()->with('error', 'Unauthorised');
    }

    return Inertia("report/report68/View", ['view' => $debitnote]);
  }

  /**
   * This is for 6.8 pdf shows
   */
  public function pdf(Request $request)
  {
    $user    = Auth::user();
    $isAdmin = in_array(optional($user->role)->name, ['admin']);

    $debitnote = $this->debitnote->with(['client.division', 'client.user', 'client.user.role', 'item', 'unit'])
      ->findOrFail($request->query('id'));

    if (!$isAdmin && ($debitnote->client?->user_id !== $user->id)) {
      return back()->with('error', 'Unauthorised');
    }

    $html = view('pdf.report68', [
      'view' => $debitnote,
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
    $fileName = "filename=report68-{$debitnote?->client->bin}-" . Carbon::parse($debitnote?->date_of_submmission)->format('M-Y') . '.pdf';

    return response($pdfData)
      ->header('Content-Type', 'application/pdf')
      ->header('Content-Disposition', "inline; $fileName");
  }
}
