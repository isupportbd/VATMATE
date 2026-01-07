<?php

namespace Modules\Dashboard\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Modules\Client\Models\Client;
use Modules\Form621\Models\Form621;

class DashboardController extends Controller
{
  public $form621;
  public $client;
  public function __construct(Form621 $form621, Client $client)
  {
    $this->form621 = $form621;
    $this->client = $client;
  }

  public function index(Request $request)
  {
    $totalClient = $this->client->count();

    $currentMonthClient = $this->client->whereRaw("created_at >= date_trunc('month', CURRENT_DATE) AND created_at < date_trunc('month', CURRENT_DATE) + INTERVAL '1 month'")->count();

    $previousMonthClient = $this->client->whereRaw("created_at >= date_trunc('month', CURRENT_DATE) - INTERVAL '1 month' AND created_at < date_trunc('month', CURRENT_DATE)")->count();

    $twoMonthsAgoClient = $this->client->whereRaw("created_at >= date_trunc('month', CURRENT_DATE) - INTERVAL '2 month' AND created_at < date_trunc('month', CURRENT_DATE) - INTERVAL '1 month'")->count();

    return Inertia::render('dashboard/index', [
      'total_client' => $totalClient,
      'current_month_client' => $currentMonthClient,
      'previous_month_client' => $previousMonthClient,
      'two_month_ago_client' => $twoMonthsAgoClient,
    ]);
  }
}
