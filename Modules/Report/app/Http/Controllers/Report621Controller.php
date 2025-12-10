<?php

namespace Modules\Report\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\Client\Models\Client;
use Modules\Form621\Models\Form621;
use Modules\Report\Jobs\Report621ExportJob;

class Report621Controller extends Controller
{
  public $form621;
  public $client;
  public function __construct(Form621 $form621, Client $client)
  {
    $this->form621 = $form621;
    $this->client = $client;
  }

  /**
   * It is created for a individual client
   * data show. it is genuinly 6.2.1
   */
  public function index(Request $request,  $client_id, $monthYear)
  {
    $user = Auth::user();
    $isAdmin = in_array(optional($user->role)->name, ['admin']);

    $itemId = $request->input('item_id');

    $client = $this->client->findOrFail($client_id);

    if (!$isAdmin && $client->user_id !== $user->id) {
      abort(403, 'Unauthorised'); // <-- hard block, no data
    }

    if ($request->has('filter')) {
      $request->validate(['item_id' => 'nullable|numeric|gt:0']);
    }

    $form621 = $this->form621->with(['importable', 'item', 'unit', 'client', 'form43'])
      ->when(!$isAdmin, fn($query) => $query->whereHas('client', fn($q) => $q->where('user_id', $user->id)))
      ->when($itemId, fn($q) => $q->where('item_id', $itemId))
      ->where('client_id', $client_id)
      ->where(DB::raw("DATE_FORMAT(date, '%Y-%m')"), $monthYear)
      ->orderBy('id', 'desc')
      ->paginate((int)request('size', 10))
      ->withQueryString();

    return Inertia("report/report621/index", [
      'Report621' => $form621,
      'clientdetail' => $client,
      'monthYear' => $monthYear,
      'item_id' => $itemId,
    ]);
  }

  public function Report621Excel(Request $request)
  {
    $user     = Auth::user();
    $isAdmin = in_array(optional($user->role)->name, ['admin']);
    $clientId   = $request->input('client_id');
    $monthYear   = $request->input('monthYear');
    $itemId     = $request->input('item_id');

    if ($request->has('filter')) {
      $request->validate([
        'client_id'   => 'required|numeric|gt:0',
        'item_id'     => 'required|numeric|gt:0',
        'monthYear'   => 'required|date_format:Y-m',
      ]);
    }

    $client = $this->client->where('id', $clientId)->when(!$isAdmin, fn($q) => $q->where('user_id', $user->id))->first();

    if (!$client) {
      return response()->json(['err', 'Unauthorized']);
    }

    $hash = substr(md5($user->id . now()), 0, 8);
    $fileName = Str::slug(optional($client)->company_name, '_') . '_' . ($monthYear ?? 'nodate') . '_' . 'report621_' . $hash . '.xlsx';
    Report621ExportJob::dispatch($clientId, $itemId, $monthYear, $fileName, $user->id);

    return response()->json(['file' => $fileName]);
  }

  public function Report621DownloadExcel($file_name)
  {
    if (!str_contains($file_name, "report621_")) {
      abort(403, 'Unauthorized export access');
    }

    if (!Storage::disk('public')->exists("temp_exports/{$file_name}")) {
      abort(404, 'File not found');
    }

    $path = Storage::disk('public')->path("temp_exports/{$file_name}");
    return response()->download($path)->deleteFileAfterSend(true);
  }
}
