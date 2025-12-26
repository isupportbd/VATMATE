<?php

namespace Modules\Report\Http\Controllers;

use App\Events\CommonEvent;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Modules\Report\Models\Report;

class FileController extends Controller
{
  public $report;
  public function __construct(Report $report)
  {
    $this->report = $report;
  }

  public function index(Request $request)
  {
    $search = trim((string) $request->input('search', ''));
    $termLike   = $search !== '' ? "%$search%" : null;

    $files = $this->report->when($search !== '', fn($query) => $query->where('name', 'ilike', $termLike))
      ->orderBy('id', 'desc')
      ->paginate((int)request('size', 10))
      ->appends($request->all());

    return Inertia('report/files/index', ['files' => $files, 'search' => $search]);
  }

  public function store(Request $request)
  {
    $user = Auth::user();

    $request->validate(
      ['file' => 'required|file|mimes:xlsx,xlsb|max:10240'], // 10MB max
      [
        'file.required' => 'File required',
        'file.file' => 'File must be a valid file',
        'file.mimes' => 'File must be an xlsx or xlsb file',
        'file.max' => 'File size must be less than 10MB'
      ]
    );

    $file = $request->file('file');
    $path = $file->store('uploads');

    $this->report->create([
      'name' => $file->getClientOriginalName(),
      'path' => $path,
      'size' => $file->getSize()
    ]);

    // realtime data
    event(new CommonEvent($user->id));

    return back()->with('message', 'File stored');
  }

  public function destroy(Report $file)
  {
    $user = Auth::user();

    Storage::delete($file->path);
    $file->delete();

    // realtime data
    event(new CommonEvent($user->id));

    return back()->with('message', 'File deleted');
  }

  private function formatSize($bytes)
  {
    $units = ['B', 'KB', 'MB', 'GB'];
    $i = 0;
    while ($bytes >= 1024 && $i < count($units) - 1) {
      $bytes /= 1024;
      $i++;
    }
    return round($bytes, 2) . ' ' . $units[$i];
  }

  public function download(Report $file)
  {
    return Storage::download($file->path, $file->name);
  }
}
