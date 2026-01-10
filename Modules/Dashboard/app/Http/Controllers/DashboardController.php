<?php

namespace Modules\Dashboard\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\ChecksUserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Modules\Adjustment\Models\Adjustment;
use Modules\Client\Models\Client;
use Modules\Creditnote\Models\Creditnote;
use Modules\Debitnote\Models\Debitnote;
use Modules\Expense\Models\Expense;
use Modules\Form44\Models\Form44;
use Modules\Form45\Models\Form45;
use Modules\Form621\Models\Form621;
use Modules\Mushaknote\Models\Mushaknote;
use Modules\Purchase\Models\Purchase;
use Modules\Sale\Models\Sale;
use Modules\Treasury\Models\Treasury;

class DashboardController extends Controller
{
  use ChecksUserRole;

  public $client;
  public $sale;
  public $purchase;
  public $adjustment;
  public $expense;
  public $treasury;
  public $mushaknote;
  public $creditnote;
  public $debitnote;
  public $form621;
  public $form44;
  public $form45;
  public function __construct(Client $client, Sale $sale, Purchase $purchase, Adjustment $adjustment, Expense $expense, Treasury $treasury, Mushaknote $mushaknote, Creditnote $creditnote, Debitnote $debitnote, Form621 $form621, Form44 $form44, Form45 $form45)
  {
    $this->client = $client;
    $this->sale = $sale;
    $this->purchase = $purchase;
    $this->adjustment = $adjustment;
    $this->expense = $expense;
    $this->treasury = $treasury;
    $this->mushaknote = $mushaknote;
    $this->creditnote = $creditnote;
    $this->debitnote = $debitnote;
    $this->form621 = $form621;
    $this->form44 = $form44;
    $this->form45 = $form45;
  }

  public function index(Request $request)
  {
    $user = Auth::user();
    $client_id = $request->input('client_id') ?? null;
    $monthYear = $request->input('monthYear') ?? null;
    /**
     * -----------------------------
     * SECTION - 1 (your old index)
     * user filter for top dashboard cards
     * -----------------------------
     */
    $userId = $this->hasRole(['admin']) ? (int) $request->input('user_id') : $user->id;

    $currentTransactionClient = $this->form621
      ->whereRaw("
        created_at >= date_trunc('month', CURRENT_DATE)
        AND created_at < date_trunc('month', CURRENT_DATE) + INTERVAL '1 month'
      ")
      ->when($userId, fn($q) => $q->whereHas('client', fn($q) => $q->where('user_id', $userId)))
      ->distinct('client_id')
      ->count('client_id');

    $previousTransactionClient = $this->form621
      ->whereRaw("
            created_at >= date_trunc('month', CURRENT_DATE) - INTERVAL '1 month'
            AND created_at < date_trunc('month', CURRENT_DATE)
        ")
      ->when($userId, fn($q) => $q->whereHas('client', fn($q) => $q->where('user_id', $userId)))
      ->distinct('client_id')
      ->count('client_id');

    $twoPreviousTransactionClient = $this->form621
      ->whereRaw("
            created_at >= date_trunc('month', CURRENT_DATE) - INTERVAL '2 month'
            AND created_at < date_trunc('month', CURRENT_DATE) - INTERVAL '1 month'
        ")
      ->when($userId, fn($q) => $q->whereHas('client', fn($q) => $q->where('user_id', $userId)))
      ->distinct('client_id')
      ->count('client_id');

    $totalClient = $this->client
      ->when($userId, fn($q) => $q->where('user_id', $userId))
      ->count();

    $currentMonthClient = $this->client
      ->whereRaw("
            created_at >= date_trunc('month', CURRENT_DATE)
            AND created_at < date_trunc('month', CURRENT_DATE) + INTERVAL '1 month'
        ")
      ->when($userId, fn($q) => $q->where('user_id', $userId))
      ->count();

    $previousMonthClient = $this->client
      ->whereRaw("
        created_at >= date_trunc('month', CURRENT_DATE) - INTERVAL '1 month'
        AND created_at < date_trunc('month', CURRENT_DATE)
      ")
      ->when($userId, fn($q) => $q->where('user_id', $userId))
      ->count();

    $twoMonthsAgoClient = $this->client
      ->whereRaw("
            created_at >= date_trunc('month', CURRENT_DATE) - INTERVAL '2 month'
            AND created_at < date_trunc('month', CURRENT_DATE) - INTERVAL '1 month'
        ")
      ->when($userId, fn($q) => $q->where('user_id', $userId))
      ->count();


    /**
     * -----------------------------
     * SECTION - 2 (your old show)
     * client + monthYear filter on same page
     * -----------------------------
     */
    // allow both route params OR query params
    $client_id = $client_id ?? $request->input('client_id');
    $monthYear = $monthYear ?? $request->input('monthYear');

    $summary = null;

    if (!empty($client_id) && !empty($monthYear)) {

      // --------- BLOCK IF CLIENT NOT BELONGS TO USER ----------
      $client = $this->client->findOrFail($client_id);

      if (!$this->hasRole(['admin']) && $client->user_id !== $user->id) {
        abort(403, 'Unauthorised'); // <-- hard block, no data
      }

      $period     = date('Y-m', strtotime($monthYear));
      $prevPeriod = date('Y-m', strtotime($period . '-01 -1 month'));

      // ----- SALE (grouped) -----
      $sale = $this->sale->selectRaw("
        MIN(item_id) as item_id,
        MIN(unit_id) as unit_id,
        sales.vat as vat_rate,
        ROUND(COALESCE(SUM(quantity), 0), 2)      AS quantity,
        ROUND(COALESCE(SUM(item_price), 0), 2)    AS item_price,
        ROUND(COALESCE(SUM(sd_value), 0), 2)      AS sd_value,
        ROUND(COALESCE(SUM(vat_value), 0), 2)     AS vat_value,
        MIN(TO_CHAR(date, 'YYYY-MM')) AS tax_period
      ")->leftJoin('items as item', 'item_id', '=', 'item.id')
        ->with(['item', 'unit'])
        ->where('client_id', $client_id)
        ->whereRaw("TO_CHAR(date, 'YYYY-MM') = ?", [$period])
        ->groupBy('sales.vat', 'item.hs_code')
        ->get();

      // ----- PURCHASE (grouped) -----
      $purchase = $this->purchase->selectRaw("
        MIN(item_id) as item_id,
        MIN(mushaknote_id) as mushaknote_id,
        MIN(unit_id) as unit_id,
        MIN(purchases.vat) as vat_rate,
        ROUND(COALESCE(SUM(total_quantity), 0), 2)   AS total_quantity,
        ROUND(COALESCE(SUM(assessable_value), 0), 2) AS assessable_value,
        ROUND(COALESCE(SUM(base_value), 0), 2)       AS base_value,
        ROUND(COALESCE(SUM(sd_value), 0), 2)         AS sd_value,
        ROUND(COALESCE(SUM(vat_value), 0), 2)        AS vat_value,
        ROUND(COALESCE(SUM(at_value), 0), 2)         AS at_value,
        MIN(TO_CHAR(entry_date, 'YYYY-MM'))          AS tax_period
      ")
        ->leftJoin('items as item', 'item_id', '=', 'item.id')
        ->leftJoin('mushaknotes as mushaknote', 'mushaknote_id', '=', 'mushaknote.id')
        ->with(['item', 'mushaknote', 'unit'])
        ->where('client_id', $client_id)
        ->whereRaw("TO_CHAR(entry_date, 'YYYY-MM') = ?", [$period])
        ->groupBy('mushaknote.name_note_no', 'item.hs_code')
        ->get();

      // ----- SALE SUM -----
      $saleSum = $this->sale
        ->where('client_id', $client_id)
        ->whereRaw("TO_CHAR(date, 'YYYY-MM') = ?", [$period])
        ->selectRaw("
        ROUND(COALESCE(SUM(item_price), 0), 2) AS item_price_sum,
        ROUND(COALESCE(SUM(sd_value), 0), 2)   AS sd_value_sum,
        ROUND(COALESCE(SUM(vat_value), 0), 2)  AS vat_value_sum
      ")->first();

      // ----- PURCHASE SUM -----
      $purchaseSum = $this->purchase
        ->where('client_id', $client_id)
        ->whereRaw("TO_CHAR(entry_date, 'YYYY-MM') = ?", [$period])
        ->selectRaw("
        ROUND(COALESCE(SUM(assessable_value), 0), 2) AS assessable_value_sum,
        ROUND(COALESCE(SUM(base_value), 0), 2)       AS base_value_sum,
        ROUND(COALESCE(SUM(sd_value), 0), 2)         AS sd_value_sum,
        ROUND(COALESCE(SUM(vat_value), 0), 2)        AS vat_value_sum,
        ROUND(COALESCE(SUM(at_value), 0), 2)         AS at_value_sum
      ")->first();

      // ----- ADJUSTMENT (all current) -----
      $adjustment = $this->adjustment
        ->selectRaw("client_id, expense_id, tax_period, ROUND(COALESCE(SUM(amount), 0), 2) AS amount")
        ->leftJoin('expenses as expense', 'expense_id', '=', 'expense.id')
        ->with('expense')
        ->where('client_id', $client_id)
        ->where('tax_period', $period)
        ->groupBy('client_id', 'expense_id', 'tax_period')
        ->get();

      $adjustment = $adjustment ?: (object)['amount' => 0];

      // Expense IDs
      $expenseIds = $this->expense
        ->whereIn('name', ['Closing Balance', 'Increase', 'Decrease', 'Penalty'])
        ->pluck('id', 'name');

      // ----- TREASURY -----
      $treasury = $this->treasury
        ->selectRaw("client_id, branch_id, tax_period, ROUND(COALESCE(SUM(amount), 0), 2) AS amount")
        ->with(['branch', 'branch.bank', 'branch.division'])
        ->where('client_id', $client_id)
        ->where('tax_period', $period)
        ->groupBy('client_id', 'branch_id', 'tax_period')
        ->first();

      $treasury = $treasury ?: (object)['amount' => 0];

      // ----- TOTAL SALES VAT -----
      $salesVatValue = optional($saleSum)->vat_value_sum ?? 0;

      // ----- NOTE 15 VAT -----
      $mushak15 = $this->mushaknote->where('name_note_no', 'note_15')->first();
      $note15   = null;

      if ($mushak15) {
        $note15 = $this->purchase
          ->selectRaw("client_id, ROUND(COALESCE(SUM(vat_value), 0), 2) AS vat_value")
          ->where('client_id', $client_id)
          ->whereRaw("TO_CHAR(entry_date, 'YYYY-MM') = ?", [$period])
          ->where('mushaknote_id', $mushak15->id)
          ->groupBy('client_id')
          ->first();
      }

      $note15Vat = optional($note15)->vat_value ?? 0;

      // ----- TOTAL AT -----
      $totalAtValue = $this->purchase
        ->where('client_id', $client_id)
        ->whereRaw("TO_CHAR(entry_date, 'YYYY-MM') = ?", [$period])
        ->sum('at_value');

      $totalAtValue = round($totalAtValue ?? 0, 2);

      // ----- INCREASING / DECREASING / PENALTY -----
      $increasingId = $expenseIds['Increase'] ?? null;
      $decreasingId = $expenseIds['Decrease'] ?? null;
      $penaltyId    = $expenseIds['Penalty'] ?? null;

      $totalIncreasing = $increasingId ? $this->adjustment
        ->selectRaw("client_id, expense_id, tax_period, ROUND(COALESCE(SUM(amount), 0), 2) AS amount")
        ->with('expense')
        ->where('client_id', $client_id)
        ->where('tax_period', $period)
        ->where('expense_id', $increasingId)
        ->groupBy('client_id', 'expense_id', 'tax_period')
        ->first() : null;

      $totalIncreasing = $totalIncreasing ?: (object)['amount' => 0];

      $totalDecreasing = $decreasingId ? $this->adjustment
        ->selectRaw("client_id, expense_id, tax_period, ROUND(COALESCE(SUM(amount), 0), 2) AS amount")
        ->with('expense')
        ->where('client_id', $client_id)
        ->where('tax_period', $period)
        ->where('expense_id', $decreasingId)
        ->groupBy('client_id', 'expense_id', 'tax_period')
        ->first() : null;

      $totalDecreasing = $totalDecreasing ?: (object)['amount' => 0];

      $totalPenalty = $penaltyId ? $this->adjustment
        ->selectRaw("client_id, expense_id, tax_period, ROUND(COALESCE(SUM(amount), 0), 2) AS amount")
        ->with('expense')
        ->where('client_id', $client_id)
        ->where('tax_period', $period)
        ->where('expense_id', $penaltyId)
        ->groupBy('client_id', 'expense_id', 'tax_period')
        ->first() : null;

      $totalPenalty = $totalPenalty ?: (object)['amount' => 0];

      // ----- TOTAL CREDITNOTE -----
      $totalCreditnote = $this->creditnote
        ->selectRaw("
        client_id,
        TO_CHAR(issued_date, 'YYYY-MM') AS tax_period,
        ROUND(COALESCE(SUM(vat_of_decreasing_adjustment), 0), 2) AS decrease_vat,
        ROUND(COALESCE(SUM(sd_of_decreasing_adjustment), 0), 2) AS decrease_sd
      ")
        ->where('client_id', $client_id)
        ->whereRaw("TO_CHAR(issued_date, 'YYYY-MM') = ?", [$period])
        ->groupBy('client_id', DB::raw("TO_CHAR(issued_date, 'YYYY-MM')"))
        ->first();

      $totalCreditnote = $totalCreditnote ?: (object)['decrease_vat' => 0, 'decrease_sd' => 0];

      // ----- TOTAL DEBITNOTE -----
      $totalDebitnote = $this->debitnote
        ->selectRaw("
        client_id,
        TO_CHAR(issued_date, 'YYYY-MM') AS tax_period,
        ROUND(COALESCE(SUM(vat_of_increasing_adjustment), 0), 2) AS increase_vat,
        ROUND(COALESCE(SUM(sd_of_increasing_adjustment), 0), 2) AS increase_sd
      ")
        ->where('client_id', $client_id)
        ->whereRaw("TO_CHAR(issued_date, 'YYYY-MM') = ?", [$period])
        ->groupBy('client_id', DB::raw("TO_CHAR(issued_date, 'YYYY-MM')"))
        ->first();

      $totalDebitnote = $totalDebitnote ?: (object)['increase_vat' => 0];

      // ----- TOTAL FORM 4.4 -----
      $totalForm44 = $this->form44
        ->selectRaw("
        client_id,
        TO_CHAR(date, 'YYYY-MM') AS tax_period,
        ROUND(COALESCE(SUM(net_increase), 0), 2) AS increase_vat
      ")
        ->where('client_id', $client_id)
        ->whereRaw("TO_CHAR(date, 'YYYY-MM') = ?", [$period])
        ->groupBy('client_id', DB::raw("TO_CHAR(date, 'YYYY-MM')"))
        ->first();

      $totalForm44 = $totalForm44 ?: (object)['increase_vat' => 0];

      // ----- TOTAL FORM 4.5 -----
      $totalForm45 = $this->form45
        ->selectRaw("
        client_id,
        TO_CHAR(date, 'YYYY-MM') AS tax_period,
        ROUND(COALESCE(SUM(net_increase), 0), 2) AS increase_vat
      ")
        ->where('client_id', $client_id)
        ->whereRaw("TO_CHAR(date, 'YYYY-MM') = ?", [$period])
        ->groupBy('client_id', DB::raw("TO_CHAR(date, 'YYYY-MM')"))
        ->first();

      $totalForm45 = $totalForm45 ?: (object)['increase_vat' => 0];

      // ----- NOTE 22 VAT -----
      $mushak22 = $this->mushaknote->where('name_note_no', 'note_22')->first();
      $note22Purchase   = null;
      $note22Sale   = null;

      if ($mushak22) {
        $note22Purchase = $this->purchase
          ->selectRaw("client_id, ROUND(COALESCE(SUM(at_value), 0), 2) AS at_value")
          ->where('client_id', $client_id)
          ->whereRaw("TO_CHAR(entry_date, 'YYYY-MM') = ?", [$period])
          ->where('mushaknote_id', $mushak22->id)
          ->groupBy('client_id')
          ->first();

        $note22Sale = $this->sale
          ->where('client_id', $client_id)
          ->whereRaw("TO_CHAR(date, 'YYYY-MM') = ?", [$period])
          ->selectRaw("ROUND(COALESCE(SUM(vat_value), 0), 2)  AS vat_value")
          ->where('vat', optional($mushak22)->percent)
          ->first();
      }

      $note22Purchase = optional($note22Purchase)->at_value ?? 0;
      $note22Sale     = optional($note22Sale)->vat_value ?? 0;

      $totalIncAdj = $note22Purchase
        + optional($totalDebitnote)->increase_vat
        + optional($totalForm44)->increase_vat
        + optional($totalForm45)->increase_vat
        + optional($totalIncreasing)->amount ?? 0;

      $totalDecAdj = $totalAtValue
        + optional($totalCreditnote)->decrease_vat
        + optional($totalDecreasing)->amount
        + $note22Sale ?? 0;

      // ----- PREVIOUS CLOSING BALANCE (FIRST MONTH FIX) -----
      $closingId      = $expenseIds['Closing Balance'] ?? null;
      $prevNetPayable = 0;
      $closignBalance = (object)['amount' => 0];

      if ($closingId) {
        $prevClosing = $this->adjustment
          ->select('client_id', 'expense_id', 'tax_period', 'amount')
          ->where('client_id', $client_id)
          ->where('expense_id', $closingId)
          ->where('tax_period', '<=', $prevPeriod)
          ->orderBy('tax_period', 'desc')
          ->first();

        $prevNetPayable = optional($prevClosing)->amount ?? 0;
        $closignBalance = (object)['amount' => $prevNetPayable];
      }

      // ----- NET PAYABLE VAT (CURRENT PERIOD ONLY) -----
      $inc   = $totalIncAdj;
      $dec   = $totalDecAdj;
      $pen   = optional($totalPenalty)->amount ?? 0;
      $at    = $totalAtValue;
      $treas = optional($treasury)->amount ?? 0;
      $crn   = optional($totalCreditnote)->decrease_vat ?? 0;
      $dbn   = optional($totalDebitnote)->increase_vat ?? 0;
      $f44   = optional($totalForm44)->increase_vat ?? 0;
      $f45   = optional($totalForm45)->increase_vat ?? 0;

      $note34 = round($salesVatValue - $note15Vat + $inc - $dec, 2);
      $note35 = $note34 - $prevNetPayable;

      $note36 = optional($saleSum)->sd_value_sum
        + optional($totalDebitnote)->increase_sd
        - optional($totalCreditnote)->decrease_sd;

      $netPayableSettlement = round($note35 + $pen, 2) ?? 0;

      $netPayableRegular = round(
        $salesVatValue - $note15Vat
          + optional($totalIncreasing)->amount + $dbn + $f44 + $f45
          - optional($totalDecreasing)->amount - $at - $crn
          + $pen
          - $treas,
        2
      ) ?? 0;

      $closignBalanceCurrentRegular    = round(-$netPayableRegular + $prevNetPayable, 2);
      $closignBalanceCurrentSettlement = round(optional($treasury)->amount - $netPayableSettlement, 2);

      if ($closingId) {
        $this->adjustment->updateOrCreate(
          [
            'client_id'  => $client_id,
            'expense_id' => $closingId,
            'tax_period' => $period,
          ],
          [
            'amount'     => $closignBalanceCurrentRegular,
          ]
        );
      }

      // ----- TOTAL PURCHASE QUANTITY -----
      $totalPurchaseQuantity = $this->purchase
        ->where('client_id', $client_id)
        ->whereRaw("TO_CHAR(entry_date, 'YYYY-MM') = ?", [$period])
        ->sum('quantity');

      $totalPurchaseQuantity = round($totalPurchaseQuantity ?? 0, 2);

      // ----- TOTAL BOE COUNT -----
      $totalBoe = $this->purchase
        ->where('client_id', $client_id)
        ->whereRaw("TO_CHAR(entry_date, 'YYYY-MM') = ?", [$period])
        ->count('boe');

      // ----- TOTAL MCU SUM -----
      $totalMcu = $this->purchase
        ->where('client_id', $client_id)
        ->whereRaw("TO_CHAR(entry_date, 'YYYY-MM') = ?", [$period])
        ->sum('mcu_amount');

      $totalMcu = round($totalMcu ?? 0, 2);

      // ----- PURCHASE DETAILS -----
      $purchaseDetails = $this->purchase
        ->with(['item', 'mushaknote', 'unit', 'stationcode'])
        ->where('client_id', $client_id)
        ->whereRaw("TO_CHAR(entry_date, 'YYYY-MM') = ?", [$period])
        ->get();

      $summary = [
        'client'                         => $client,
        'sale'                           => $sale,
        'purchase'                       => $purchase,
        'saleSum'                        => $saleSum,
        'purchaseSum'                    => $purchaseSum,
        'adjustment'                     => $adjustment,
        'closignBalance'                 => $closignBalance,
        'treasury'                       => number_format(optional($treasury)->amount, 2, '.', ''),
        'netPayableSettlement'           => number_format($netPayableSettlement, 2, '.', ''),
        'netPayableRegular'              => number_format($netPayableRegular, 2, '.', ''),
        'prevNetPayable'                 => number_format($prevNetPayable, 2, '.', ''),
        'closignBalanceCurrentRegular'   => number_format($closignBalanceCurrentRegular, 2, '.', ''),
        'closignBalanceCurrentSettlement' => number_format($closignBalanceCurrentSettlement, 2, '.', ''),
        'totalPurchaseQuantity'          => $totalPurchaseQuantity,
        'totalBoe'                       => $totalBoe,
        'totalMcu'                       => number_format($totalMcu, 2, '.', ''),
        'note22Purchase'                 => $note22Purchase,
        'note22Sale'                     => $note22Sale,
        'totalAtValue'                   => $totalAtValue,
        'purchaseDetails'                => $purchaseDetails,
        'totalCreditnote'                => number_format(optional($totalCreditnote)->decrease_vat, 2, '.', ''),
        'totalDebitnote'                 => number_format(optional($totalDebitnote)->increase_vat, 2, '.', ''),
        'totalForm44'                    => number_format(optional($totalForm44)->increase_vat, 2, '.', ''),
        'totalForm45'                    => number_format(optional($totalForm45)->increase_vat, 2, '.', ''),
        'totalIncAdj'                    => number_format($totalIncAdj, 2, '.', ''),
        'totalDecAdj'                    => number_format($totalDecAdj, 2, '.', ''),
        'totalIncreasing'                => $totalIncreasing,
        'totalDecreasing'                => $totalDecreasing,
        'note34'                         => number_format($note34, 2, '.', ''),
        'note35'                         => number_format($note35, 2, '.', ''),
        'note36'                         => number_format($note36, 2, '.', ''),
      ];
    }

    /**
     * ✅ Single render for same page
     */
    return Inertia::render('dashboard/index', [
      'total_client' => $totalClient,
      'current_month_client' => $currentMonthClient,
      'previous_month_client' => $previousMonthClient,
      'two_month_ago_client' => $twoMonthsAgoClient,
      'current_transaction_client' => $currentTransactionClient,
      'previous_transaction_client' => $previousTransactionClient,
      'two_previous_transaction_client' => $twoPreviousTransactionClient,
      // new: summary section (null if not selected)
      'summary' => $summary,
    ]);
  }

  // public function show(Request $request, $client_id, $monthYear)
  // {
  //   $user    = Auth::user();

  //   // --------- BLOCK IF CLIENT NOT BELONGS TO USER ----------
  //   $client = $this->client->findOrFail($client_id);

  //   if (!$this->hasRole(['admin']) && $client->user_id !== $user->id) {
  //     abort(403, 'Unauthorised'); // <-- hard block, no data
  //   }

  //   $period     = date('Y-m', strtotime($monthYear));
  //   $prevPeriod = date('Y-m', strtotime($period . '-01 -1 month'));

  //   // ----- SALE (grouped) -----
  //   $sale = $this->sale->selectRaw("
  //     MIN(item_id) as item_id,
  //     MIN(unit_id) as unit_id,
  //     sales.vat as vat_rate,
  //     ROUND(COALESCE(SUM(quantity), 0), 2)      AS quantity,
  //     ROUND(COALESCE(SUM(item_price), 0), 2)    AS item_price,
  //     ROUND(COALESCE(SUM(sd_value), 0), 2)      AS sd_value,
  //     ROUND(COALESCE(SUM(vat_value), 0), 2)     AS vat_value,
  //     MIN(TO_CHAR(date, 'YYYY-MM')) AS tax_period
  //   ")->leftJoin('items as item', 'item_id', '=', 'item.id')
  //     ->with(['item', 'unit'])
  //     ->where('client_id', $client_id)
  //     ->whereRaw("TO_CHAR(date, 'YYYY-MM') = ?", [$period])
  //     ->groupBy('sales.vat', 'item.hs_code')
  //     ->get();

  //   // ----- PURCHASE (grouped) -----
  //   $purchase = $this->purchase->selectRaw("
  //     MIN(item_id) as item_id,
  //     MIN(mushaknote_id) as mushaknote_id,
  //     MIN(unit_id) as unit_id,
  //     MIN(purchases.vat) as vat_rate,
  //     ROUND(COALESCE(SUM(total_quantity), 0), 2)   AS total_quantity,
  //     ROUND(COALESCE(SUM(assessable_value), 0), 2) AS assessable_value,
  //     ROUND(COALESCE(SUM(base_value), 0), 2)       AS base_value,
  //     ROUND(COALESCE(SUM(sd_value), 0), 2)         AS sd_value,
  //     ROUND(COALESCE(SUM(vat_value), 0), 2)        AS vat_value,
  //     ROUND(COALESCE(SUM(at_value), 0), 2)         AS at_value,
  //     MIN(TO_CHAR(entry_date, 'YYYY-MM'))          AS tax_period
  //   ")
  //     ->leftJoin('items as item', 'item_id', '=', 'item.id')
  //     ->leftJoin('mushaknotes as mushaknote', 'mushaknote_id', '=', 'mushaknote.id')
  //     ->with(['item', 'mushaknote', 'unit'])
  //     ->where('client_id', $client_id)
  //     ->whereRaw("TO_CHAR(entry_date, 'YYYY-MM') = ?", [$period])
  //     ->groupBy('mushaknote.name_note_no', 'item.hs_code')
  //     ->get();

  //   // ----- SALE SUM -----
  //   $saleSum = $this->sale
  //     ->where('client_id', $client_id)
  //     ->whereRaw("TO_CHAR(date, 'YYYY-MM') = ?", [$period])
  //     ->selectRaw("
  //     ROUND(COALESCE(SUM(item_price), 0), 2) AS item_price_sum,
  //     ROUND(COALESCE(SUM(sd_value), 0), 2)   AS sd_value_sum,
  //     ROUND(COALESCE(SUM(vat_value), 0), 2)  AS vat_value_sum
  //   ")
  //     ->first();

  //   // ----- PURCHASE SUM -----
  //   $purchaseSum = $this->purchase
  //     ->where('client_id', $client_id)
  //     ->whereRaw("TO_CHAR(entry_date, 'YYYY-MM') = ?", [$period])
  //     ->selectRaw("
  //     ROUND(COALESCE(SUM(assessable_value), 0), 2) AS assessable_value_sum,
  //     ROUND(COALESCE(SUM(base_value), 0), 2)       AS base_value_sum,
  //     ROUND(COALESCE(SUM(sd_value), 0), 2)         AS sd_value_sum,
  //     ROUND(COALESCE(SUM(vat_value), 0), 2)        AS vat_value_sum,
  //     ROUND(COALESCE(SUM(at_value), 0), 2)         AS at_value_sum
  //   ")
  //     ->first();

  //   // ----- ADJUSTMENT (all current) -----
  //   $adjustment = $this->adjustment
  //     ->selectRaw("client_id, expense_id, tax_period, ROUND(COALESCE(SUM(amount), 0), 2) AS amount")
  //     ->leftJoin('expenses as expense', 'expense_id', '=', 'expense.id')
  //     ->with('expense')
  //     ->where('client_id', $client_id)
  //     ->where('tax_period', $period)
  //     ->groupBy('client_id', 'expense_id', 'tax_period')
  //     ->get();

  //   $adjustment = $adjustment ?: (object)['amount' => 0];

  //   // Expense IDs
  //   $expenseIds = $this->expense
  //     ->whereIn('name', ['Closing Balance', 'Increase', 'Decrease', 'Penalty'])
  //     ->pluck('id', 'name');

  //   // ----- TREASURY -----
  //   $treasury = $this->treasury
  //     ->selectRaw("client_id, branch_id, tax_period, ROUND(COALESCE(SUM(amount), 0), 2) AS amount")
  //     ->with(['branch', 'branch.bank', 'branch.division'])
  //     ->where('client_id', $client_id)
  //     ->where('tax_period', $period)
  //     ->groupBy('client_id', 'branch_id', 'tax_period')
  //     ->first();

  //   $treasury = $treasury ?: (object)['amount' => 0];

  //   // ----- TOTAL SALES VAT -----
  //   $salesVatValue = optional($saleSum)->vat_value_sum ?? 0;

  //   // ----- NOTE 15 VAT -----
  //   $mushak15 = $this->mushaknote->where('name_note_no', 'note_15')->first();
  //   $note15   = null;

  //   if ($mushak15) {
  //     $note15 = $this->purchase
  //       ->selectRaw("client_id, ROUND(COALESCE(SUM(vat_value), 0), 2) AS vat_value")
  //       ->where('client_id', $client_id)
  //       ->whereRaw("TO_CHAR(entry_date, 'YYYY-MM') = ?", [$period])
  //       ->where('mushaknote_id', $mushak15->id)
  //       ->groupBy('client_id')
  //       ->first();
  //   }

  //   $note15Vat = optional($note15)->vat_value ?? 0;

  //   // ----- TOTAL AT -----
  //   $totalAtValue = $this->purchase
  //     ->where('client_id', $client_id)
  //     ->whereRaw("TO_CHAR(entry_date, 'YYYY-MM') = ?", [$period])
  //     ->sum('at_value');

  //   $totalAtValue = round($totalAtValue ?? 0, 2);

  //   // ----- INCREASING / DECREASING / PENALTY -----
  //   $increasingId = $expenseIds['Increase'] ?? null;
  //   $decreasingId = $expenseIds['Decrease'] ?? null;
  //   $penaltyId    = $expenseIds['Penalty'] ?? null;

  //   $totalIncreasing = $increasingId ? $this->adjustment
  //     ->selectRaw("client_id, expense_id, tax_period, ROUND(COALESCE(SUM(amount), 0), 2) AS amount")
  //     ->with('expense')
  //     ->where('client_id', $client_id)
  //     ->where('tax_period', $period)
  //     ->where('expense_id', $increasingId)
  //     ->groupBy('client_id', 'expense_id', 'tax_period')
  //     ->first() : null;

  //   $totalIncreasing = $totalIncreasing ?: (object)['amount' => 0];

  //   $totalDecreasing = $decreasingId ? $this->adjustment
  //     ->selectRaw("client_id, expense_id, tax_period, ROUND(COALESCE(SUM(amount), 0), 2) AS amount")
  //     ->with('expense')
  //     ->where('client_id', $client_id)
  //     ->where('tax_period', $period)
  //     ->where('expense_id', $decreasingId)
  //     ->groupBy('client_id', 'expense_id', 'tax_period')
  //     ->first() : null;

  //   $totalDecreasing = $totalDecreasing ?: (object)['amount' => 0];

  //   $totalPenalty = $penaltyId ? $this->adjustment
  //     ->selectRaw("client_id, expense_id, tax_period, ROUND(COALESCE(SUM(amount), 0), 2) AS amount")
  //     ->with('expense')
  //     ->where('client_id', $client_id)
  //     ->where('tax_period', $period)
  //     ->where('expense_id', $penaltyId)
  //     ->groupBy('client_id', 'expense_id', 'tax_period')
  //     ->first() : null;

  //   $totalPenalty = $totalPenalty ?: (object)['amount' => 0];

  //   // ----- TOTAL CREDITNOTE -----
  //   $totalCreditnote = $this->creditnote
  //     ->selectRaw("
  //     client_id,
  //     TO_CHAR(issued_date, 'YYYY-MM') AS tax_period,
  //     ROUND(COALESCE(SUM(vat_of_decreasing_adjustment), 0), 2) AS decrease_vat,
  //     ROUND(COALESCE(SUM(sd_of_decreasing_adjustment), 0), 2) AS decrease_sd
  //   ")
  //     ->where('client_id', $client_id)
  //     ->whereRaw("TO_CHAR(issued_date, 'YYYY-MM') = ?", [$period])
  //     ->groupBy('client_id', DB::raw("TO_CHAR(issued_date, 'YYYY-MM')"))
  //     ->first();

  //   $totalCreditnote = $totalCreditnote ?: (object)['decrease_vat' => 0, 'decrease_sd' => 0];

  //   // ----- TOTAL DEBITNOTE -----
  //   $totalDebitnote = $this->debitnote
  //     ->selectRaw("
  //     client_id,
  //     TO_CHAR(issued_date, 'YYYY-MM') AS tax_period,
  //     ROUND(COALESCE(SUM(vat_of_increasing_adjustment), 0), 2) AS increase_vat,
  //     ROUND(COALESCE(SUM(sd_of_increasing_adjustment), 0), 2) AS increase_sd
  //   ")
  //     ->where('client_id', $client_id)
  //     ->whereRaw("TO_CHAR(issued_date, 'YYYY-MM') = ?", [$period])
  //     ->groupBy('client_id', DB::raw("TO_CHAR(issued_date, 'YYYY-MM')"))
  //     ->first();

  //   $totalDebitnote = $totalDebitnote ?: (object)['increase_vat' => 0];

  //   // ----- TOTAL FORM 4.4 -----
  //   $totalForm44 = $this->form44
  //     ->selectRaw("
  //     client_id,
  //     TO_CHAR(date, 'YYYY-MM') AS tax_period,
  //     ROUND(COALESCE(SUM(net_increase), 0), 2) AS increase_vat
  //   ")
  //     ->where('client_id', $client_id)
  //     ->whereRaw("TO_CHAR(date, 'YYYY-MM') = ?", [$period])
  //     ->groupBy('client_id', DB::raw("TO_CHAR(date, 'YYYY-MM')"))
  //     ->first();

  //   $totalForm44 = $totalForm44 ?: (object)['increase_vat' => 0];

  //   // ----- TOTAL FORM 4.5 -----
  //   $totalForm45 = $this->form45
  //     ->selectRaw("
  //     client_id,
  //     TO_CHAR(date, 'YYYY-MM') AS tax_period,
  //     ROUND(COALESCE(SUM(net_increase), 0), 2) AS increase_vat
  //   ")
  //     ->where('client_id', $client_id)
  //     ->whereRaw("TO_CHAR(date, 'YYYY-MM') = ?", [$period])
  //     ->groupBy('client_id', DB::raw("TO_CHAR(date, 'YYYY-MM')"))
  //     ->first();

  //   $totalForm45 = $totalForm45 ?: (object)['increase_vat' => 0];

  //   // ----- NOTE 22 VAT -----
  //   $mushak22 = $this->mushaknote->where('name_note_no', 'note_22')->first();
  //   $note22Purchase   = null;
  //   $note22Sale   = null;

  //   if ($mushak22) {
  //     $note22Purchase = $this->purchase
  //       ->selectRaw("client_id, ROUND(COALESCE(SUM(at_value), 0), 2) AS at_value")
  //       ->where('client_id', $client_id)
  //       ->whereRaw("TO_CHAR(entry_date, 'YYYY-MM') = ?", [$period])
  //       ->where('mushaknote_id', $mushak22->id)
  //       ->groupBy('client_id')
  //       ->first();

  //     $note22Sale = $this->sale
  //       ->where('client_id', $client_id)
  //       ->whereRaw("TO_CHAR(date, 'YYYY-MM') = ?", [$period])
  //       ->selectRaw("ROUND(COALESCE(SUM(vat_value), 0), 2)  AS vat_value")
  //       ->where('vat', optional($mushak22)->percent)
  //       ->first();
  //   }

  //   $note22Purchase = optional($note22Purchase)->at_value ?? 0;
  //   $note22Sale     = optional($note22Sale)->vat_value ?? 0;

  //   $totalIncAdj = $note22Purchase
  //     + optional($totalDebitnote)->increase_vat
  //     + optional($totalForm44)->increase_vat
  //     + optional($totalForm45)->increase_vat
  //     + optional($totalIncreasing)->amount ?? 0;

  //   $totalDecAdj = $totalAtValue
  //     + optional($totalCreditnote)->decrease_vat
  //     + optional($totalDecreasing)->amount
  //     + $note22Sale ?? 0;

  //   // ----- PREVIOUS CLOSING BALANCE (FIRST MONTH FIX) -----
  //   $closingId      = $expenseIds['Closing Balance'] ?? null;
  //   $prevNetPayable = 0;
  //   $closignBalance = (object)['amount' => 0];

  //   if ($closingId) {
  //     // Get the LATEST closing balance whose tax_period is <= prevPeriod
  //     // For first month (July) this will pick June  (your 664.18)
  //     // For August it will pick July (saved by system), etc.
  //     $prevClosing = $this->adjustment
  //       ->select('client_id', 'expense_id', 'tax_period', 'amount')
  //       ->where('client_id', $client_id)
  //       ->where('expense_id', $closingId)
  //       ->where('tax_period', '<=', $prevPeriod)
  //       ->orderBy('tax_period', 'desc')
  //       ->first();

  //     $prevNetPayable = optional($prevClosing)->amount ?? 0;
  //     $closignBalance = (object)['amount' => $prevNetPayable];
  //   }

  //   // ----- NET PAYABLE VAT (CURRENT PERIOD ONLY) -----
  //   $inc   = $totalIncAdj;
  //   $dec   = $totalDecAdj;
  //   $pen   = optional($totalPenalty)->amount ?? 0;
  //   $at    = $totalAtValue;
  //   $treas = optional($treasury)->amount ?? 0;
  //   $crn   = optional($totalCreditnote)->decrease_vat ?? 0;
  //   $dbn   = optional($totalDebitnote)->increase_vat ?? 0;
  //   $f44   = optional($totalForm44)->increase_vat ?? 0;
  //   $f45   = optional($totalForm45)->increase_vat ?? 0;

  //   $note34 = round($salesVatValue - $note15Vat + $inc - $dec, 2);

  //   $note35 = $note34 - $prevNetPayable;

  //   $note36 = optional($saleSum)->sd_value_sum
  //     + optional($totalDebitnote)->increase_sd
  //     - optional($totalCreditnote)->decrease_sd;

  //   $netPayableSettlement = round($note35 + $pen, 2) ?? 0;

  //   $netPayableRegular = round(
  //     $salesVatValue - $note15Vat
  //       + optional($totalIncreasing)->amount + $dbn + $f44 + $f45
  //       - optional($totalDecreasing)->amount - $at - $crn
  //       + $pen
  //       - $treas,
  //     2
  //   ) ?? 0;

  //   // 🔹 CURRENT MONTH CLOSING (your exact logic)
  //   // First month:
  //   //   prevNetPayable = 664.18 (June)
  //   //   closing_current = -netPayableRegular + 664.18
  //   $closignBalanceCurrentRegular    = round(-$netPayableRegular + $prevNetPayable, 2);
  //   $closignBalanceCurrentSettlement = round(optional($treasury)->amount - $netPayableSettlement, 2);

  //   // 🔹 Auto-save current month closing so next month can use it
  //   if ($closingId) {
  //     $this->adjustment->updateOrCreate(
  //       [
  //         'client_id'  => $client_id,
  //         'expense_id' => $closingId,
  //         'tax_period' => $period, // current month
  //       ],
  //       [
  //         'amount'     => $closignBalanceCurrentRegular,
  //       ]
  //     );
  //   }

  //   // ----- TOTAL PURCHASE QUANTITY -----
  //   $totalPurchaseQuantity = $this->purchase
  //     ->where('client_id', $client_id)
  //     ->whereRaw("TO_CHAR(entry_date, 'YYYY-MM') = ?", [$period])
  //     ->sum('quantity');

  //   $totalPurchaseQuantity = round($totalPurchaseQuantity ?? 0, 2);

  //   // ----- TOTAL BOE COUNT -----
  //   $totalBoe = $this->purchase
  //     ->where('client_id', $client_id)
  //     ->whereRaw("TO_CHAR(entry_date, 'YYYY-MM') = ?", [$period])
  //     ->count('boe');

  //   // ----- TOTAL MCU SUM -----
  //   $totalMcu = $this->purchase
  //     ->where('client_id', $client_id)
  //     ->whereRaw("TO_CHAR(entry_date, 'YYYY-MM') = ?", [$period])
  //     ->sum('mcu_amount');

  //   $totalMcu = round($totalMcu ?? 0, 2);

  //   // ----- PURCHASE DETAILS -----
  //   $purchaseDetails = $this->purchase
  //     ->with(['item', 'mushaknote', 'unit', 'stationcode'])
  //     ->where('client_id', $client_id)
  //     ->whereRaw("TO_CHAR(entry_date, 'YYYY-MM') = ?", [$period])
  //     ->get();

  //   return Inertia::render('dashboard/index', [
  //     'summary' => [
  //       'client'                         => $client,
  //       'sale'                           => $sale,
  //       'purchase'                       => $purchase,
  //       'saleSum'                        => $saleSum,
  //       'purchaseSum'                    => $purchaseSum,
  //       'adjustment'                     => $adjustment,
  //       'closignBalance'                 => $closignBalance,                   // previous closing (June for first month)
  //       'treasury'                       => number_format(optional($treasury)->amount, 2, '.', ''),
  //       'netPayableSettlement'           => number_format($netPayableSettlement, 2, '.', ''),
  //       'netPayableRegular'              => number_format($netPayableRegular, 2, '.', ''),
  //       'prevNetPayable'                 => number_format($prevNetPayable, 2, '.', ''),
  //       'closignBalanceCurrentRegular'   => number_format($closignBalanceCurrentRegular, 2, '.', ''),
  //       'closignBalanceCurrentSettlement' => number_format($closignBalanceCurrentSettlement, 2, '.', ''),
  //       'totalPurchaseQuantity'          => $totalPurchaseQuantity,
  //       'totalBoe'                       => $totalBoe,
  //       'totalMcu'                       => number_format($totalMcu, 2, '.', ''),
  //       'note22Purchase'                 => $note22Purchase,
  //       'note22Sale'                     => $note22Sale,
  //       'totalAtValue'                   => $totalAtValue,
  //       'purchaseDetails'                => $purchaseDetails,
  //       'totalCreditnote'                => number_format(optional($totalCreditnote)->decrease_vat, 2, '.', ''),
  //       'totalDebitnote'                 => number_format(optional($totalDebitnote)->increase_vat, 2, '.', ''),
  //       'totalForm44'                    => number_format(optional($totalForm44)->increase_vat, 2, '.', ''),
  //       'totalForm45'                    => number_format(optional($totalForm45)->increase_vat, 2, '.', ''),
  //       'totalIncAdj'                    => number_format($totalIncAdj, 2, '.', ''),
  //       'totalDecAdj'                    => number_format($totalDecAdj, 2, '.', ''),
  //       'totalIncreasing'                => $totalIncreasing,
  //       'totalDecreasing'                => $totalDecreasing,
  //       'note34'                         => number_format($note34, 2, '.', ''),
  //       'note35'                         => number_format($note35, 2, '.', ''),
  //       'note36'                         => number_format($note36, 2, '.', ''),
  //     ],
  //   ]);
  // }
}
