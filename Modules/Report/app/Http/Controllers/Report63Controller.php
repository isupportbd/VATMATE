<?php

namespace Modules\Report\Http\Controllers;

use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Modules\Client\Models\Client;
use Modules\Report\Jobs\Report63ExportJob;
use Modules\Sale\Models\Sale;

class Report63Controller extends Controller
{
  public $client;
  public $sale;
  public function __construct(Client $client, Sale $sale)
  {
    $this->client = $client;
    $this->sale = $sale;
  }

  public function index(Request $request, $client_id, $monthYear)
  {
    $user = Auth::user();
    $isAdmin = in_array(optional($user->role)->name, ['admin']);
    $itemId = $request->input('item_id');
    $period = date('Y-m', strtotime($monthYear));

    $client = $this->client->findOrFail($client_id);

    if (!$isAdmin && $client->user_id !== $user->id) {
      abort(403, 'Unauthorised'); // <-- hard block, no data
    }

    if ($request->has('filter')) {
      $request->validate(['item_id' => 'nullable|numeric|gt:0']);
    }

    $query = $this->sale->with(['client', 'item', 'unit', 'bank'])
      ->when(!$isAdmin, fn($query) => $query->whereHas('client', fn($q) => $q->where('user_id', $user->id)))
      ->where('client_id', $client_id)
      ->whereRaw('DATE_FORMAT(date, "%Y-%m") = ?', [$period])
      ->when($itemId, fn($q) => $q->where('item_id', $itemId))
      ->orderBy('id', 'desc')
      ->paginate((int)request('size', 10))
      ->withQueryString();

    return Inertia::render('report/report63/index', ['Report63' => $query, 'client' => $client]);
  }

  public function show($id)
  {
    $user    = Auth::user();
    $isAdmin = in_array(optional($user->role)->name, ['admin']);

    $query = $this->sale->with(['client', 'item', 'unit', 'bank'])->find($id);

    if (!$isAdmin && ($query->client?->user_id !== $user->id)) {
      return back()->with('error', 'Unauthorised');
    }

    return Inertia("report/report63/View", ['view' => $query]);
  }

  // in Report63Controller
  public function preview($id)
  {
    $user    = Auth::user();
    $isAdmin = in_array(optional($user->role)->name, ['admin']);

    $sale = $this->sale->with(['client', 'item', 'unit', 'bank'])->findOrFail($id);

    if (!$isAdmin && ($sale->client?->user_id !== $user->id)) {
      return back()->with('error', 'Unauthorised');
    }

    return view('pdf.report63', [
      'view' => $sale,
    ]);
  }

  public function downloadPdf(Request $request, $id)
  {
    $user    = Auth::user();
    $isAdmin = in_array(optional($user->role)->name, ['admin']);

    $sale = $this->sale->with(['client', 'item', 'unit', 'bank'])->findOrFail($id);

    if (!$isAdmin && ($sale->client?->user_id !== $user->id)) {
      return back()->with('error', 'Unauthorised');
    }

    $pdf = Pdf::loadView('pdf.report63', [
      'view' => $sale,
    ])->setPaper('a4', 'portrait');

    // For design / test:
    return $pdf->stream("report63-{$id}.pdf");

    // return $pdf->download("report63-{$id}.pdf");
  }

  public function Report63Excel(Request $request)
  {
    $user     = Auth::user();
    $isAdmin = in_array(optional($user->role)->name, ['admin']);
    $clientId   = $request->input('client_id');
    $itemId     = $request->input('item_id');
    $monthYear   = $request->input('month_year');

    if ($request->has('filter')) {
      $request->validate([
        'client_id'   => 'required|numeric|gt:0',
        'item_id'     => 'required|numeric|gt:0',
        'month_year'  => 'required|date_format:Y-m',
      ]);
    }

    $client = $this->client->where('id', $clientId)->when(!$isAdmin, fn($q) => $q->where('user_id', $user->id))->first();

    if (!$client) {
      return response()->json(['err', 'Unauthorized']);
    }

    $hash = substr(md5($user->id . now()), 0, 8);
    $fileName = Str::slug(optional($client)->company_name, '_') . '_' . $monthYear . '_' . 'report63_' . $hash . '.xlsx';
    Report63ExportJob::dispatch($clientId, $itemId, $monthYear, $fileName, $user->id);

    return response()->json(['file' => $fileName]);
  }

  public function Report63DownloadExcel($file_name)
  {
    if (!str_contains($file_name, "report63_")) {
      abort(403, 'Unauthorized export access');
    }

    if (!Storage::disk('public')->exists("temp_exports/{$file_name}")) {
      abort(404, 'File not found');
    }

    $path = Storage::disk('public')->path("temp_exports/{$file_name}");
    return response()->download($path)->deleteFileAfterSend(true);
  }
}
