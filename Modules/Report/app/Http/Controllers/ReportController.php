<?php

namespace Modules\Report\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Modules\Form621\Models\Form621;

class ReportController extends Controller
{
  public $form621;
  public function __construct(Form621 $form621)
  {
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
    $user = Auth::user();
    $isAdmin = in_array(optional($user->role)->name, ['admin']);
    $search = $request->input('search');

    $query = $this->form621->with(['importable', 'client.user', 'item', 'unit'])
      ->select('id', 'importable_id', 'client_id', 'item_id', 'unit_id', DB::raw("DATE_FORMAT(date, '%Y-%m') as tax_period"))
      ->when(!$isAdmin, fn($query) => $query->whereHas('client', fn($q) => $q->where('user_id', $user->id)))
      ->when($search, function ($query) use ($search) {
        $query->whereHas('client', function ($query) use ($search) {
          $query->where('bin', 'like', '%' . strtolower($search) . '%')
            ->orWhere('mobile', 'like', '%' . strtolower($search) . '%')
            ->orWhere('company_name', 'like', '%' . strtolower($search) . '%');
        });
      })->whereHas('client', fn($q) => $q->where('id', $client_id))
      ->orderBy('tax_period', 'desc')
      ->groupBy('client_id', DB::raw("DATE_FORMAT(date, '%Y-%m')"))
      ->paginate((int)request('size', 10))
      ->withQueryString();

    return Inertia("report/ClientDataList", ['clientdatalist' => $query, 'search' => $search]);
  }
}
