<?php

namespace Modules\Report\Http\Controllers;

use App\Events\CommonEvent;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
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
use Modules\Mushaknote\Models\Mushaknote;
use Modules\Purchase\Models\Purchase;
use Modules\Sale\Models\Sale;
use Modules\Treasury\Models\Treasury;
use Spatie\Browsershot\Browsershot;

class SummaryController extends Controller
{
  public $client;
  public $sale;
  public $purchase;
  public $adjustment;
  public $expense;
  public $treasury;
  public $mushaknote;
  public $creditnote;
  public $debitnote;
  public $form44;
  public $form45;
  public function __construct(Client $client, Sale $sale, Purchase $purchase, Adjustment $adjustment, Expense $expense, Treasury $treasury, Mushaknote $mushaknote, Creditnote $creditnote, Debitnote $debitnote, Form44 $form44, Form45 $form45)
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
    $this->form44 = $form44;
    $this->form45 = $form45;
  }

  public function index(Request $request, $client_id, $monthYear)
  {
    $user    = Auth::user();
    $isAdmin = in_array(optional($user->role)->name, ['admin']);

    // --------- BLOCK IF CLIENT NOT BELONGS TO USER ----------
    $client = $this->client->findOrFail($client_id);

    if (!$isAdmin && $client->user_id !== $user->id) {
      abort(403, 'Unauthorised'); // <-- hard block, no data
    }

    $period     = date('Y-m', strtotime($monthYear));
    $prevPeriod = date('Y-m', strtotime($period . '-01 -1 month'));

    // ----- SALE (grouped) -----
    $sale = $this->sale
      ->selectRaw("
      item_id,
      unit_id,
      sales.vat as vat_rate,
      ROUND(COALESCE(SUM(quantity), 0), 2)      AS quantity,
      ROUND(COALESCE(SUM(item_price), 0), 2)    AS item_price,
      ROUND(COALESCE(SUM(sd_value), 0), 2)      AS sd_value,
      ROUND(COALESCE(SUM(vat_value), 0), 2)     AS vat_value,
      DATE_FORMAT(date, '%Y-%m') AS tax_period
    ")
      ->leftJoin('items as item', 'item_id', '=', 'item.id')
      ->with(['item', 'unit'])
      ->where('client_id', $client_id)
      ->whereRaw('DATE_FORMAT(date, "%Y-%m") = ?', [$period])
      ->groupBy('vat_rate', 'item.hs_code')
      ->get();

    // ----- PURCHASE (grouped) -----
    $purchase = $this->purchase
      ->selectRaw("
      item_id,
      mushaknote_id,
      unit_id,
      purchases.vat as vat_rate,
      ROUND(COALESCE(SUM(total_quantity), 0), 2)   AS total_quantity,
      ROUND(COALESCE(SUM(assessable_value), 0), 2) AS assessable_value,
      ROUND(COALESCE(SUM(base_value), 0), 2)       AS base_value,
      ROUND(COALESCE(SUM(sd_value), 0), 2)         AS sd_value,
      ROUND(COALESCE(SUM(vat_value), 0), 2)        AS vat_value,
      ROUND(COALESCE(SUM(at_value), 0), 2)         AS at_value,
      DATE_FORMAT(entry_date, '%Y-%m') AS tax_period
    ")
      ->leftJoin('items as item', 'item_id', '=', 'item.id')
      ->leftJoin('mushaknotes as mushaknote', 'mushaknote_id', '=', 'mushaknote.id')
      ->with(['item', 'mushaknote', 'unit'])
      ->where('client_id', $client_id)
      ->whereRaw('DATE_FORMAT(entry_date, "%Y-%m") = ?', [$period])
      ->groupBy('mushaknote.name_note_no', 'item.hs_code')
      ->get();

    // ----- SALE SUM -----
    $saleSum = $this->sale
      ->where('client_id', $client_id)
      ->whereRaw('DATE_FORMAT(date, "%Y-%m") = ?', [$period])
      ->selectRaw("
      ROUND(COALESCE(SUM(item_price), 0), 2) AS item_price_sum,
      ROUND(COALESCE(SUM(sd_value), 0), 2)   AS sd_value_sum,
      ROUND(COALESCE(SUM(vat_value), 0), 2)  AS vat_value_sum
    ")
      ->first();

    // ----- PURCHASE SUM -----
    $purchaseSum = $this->purchase
      ->where('client_id', $client_id)
      ->whereRaw('DATE_FORMAT(entry_date, "%Y-%m") = ?', [$period])
      ->selectRaw("
      ROUND(COALESCE(SUM(assessable_value), 0), 2) AS assessable_value_sum,
      ROUND(COALESCE(SUM(base_value), 0), 2)       AS base_value_sum,
      ROUND(COALESCE(SUM(sd_value), 0), 2)         AS sd_value_sum,
      ROUND(COALESCE(SUM(vat_value), 0), 2)        AS vat_value_sum,
      ROUND(COALESCE(SUM(at_value), 0), 2)         AS at_value_sum
    ")
      ->first();

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
        ->whereRaw('DATE_FORMAT(entry_date, "%Y-%m") = ?', [$period])
        ->where('mushaknote_id', $mushak15->id)
        ->groupBy('client_id')
        ->first();
    }

    $note15Vat = optional($note15)->vat_value ?? 0;

    // ----- TOTAL AT -----
    $totalAtValue = $this->purchase
      ->where('client_id', $client_id)
      ->whereRaw('DATE_FORMAT(entry_date, "%Y-%m") = ?', [$period])
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
      DATE_FORMAT(issued_date, '%Y-%m') AS tax_period,
      ROUND(COALESCE(SUM(vat_of_decreasing_adjustment), 0), 2) AS decrease_vat,
      ROUND(COALESCE(SUM(sd_of_decreasing_adjustment), 0), 2) AS decrease_sd
    ")
      ->where('client_id', $client_id)
      ->whereRaw('DATE_FORMAT(issued_date, "%Y-%m") = ?', [$period])
      ->groupBy('client_id', DB::raw("DATE_FORMAT(issued_date, '%Y-%m')"))
      ->first();

    $totalCreditnote = $totalCreditnote ?: (object)['decrease_vat' => 0, 'decrease_sd' => 0];

    // ----- TOTAL DEBITNOTE -----
    $totalDebitnote = $this->debitnote
      ->selectRaw("
      client_id,
      DATE_FORMAT(issued_date, '%Y-%m') AS tax_period,
      ROUND(COALESCE(SUM(vat_of_increasing_adjustment), 0), 2) AS increase_vat,
      ROUND(COALESCE(SUM(sd_of_increasing_adjustment), 0), 2) AS increase_sd
    ")
      ->where('client_id', $client_id)
      ->whereRaw('DATE_FORMAT(issued_date, "%Y-%m") = ?', [$period])
      ->groupBy('client_id', DB::raw("DATE_FORMAT(issued_date, '%Y-%m')"))
      ->first();

    $totalDebitnote = $totalDebitnote ?: (object)['increase_vat' => 0];

    // ----- TOTAL FORM 4.4 -----
    $totalForm44 = $this->form44
      ->selectRaw("
      client_id,
      DATE_FORMAT(date, '%Y-%m') AS tax_period,
      ROUND(COALESCE(SUM(net_increase), 0), 2) AS increase_vat
    ")
      ->where('client_id', $client_id)
      ->whereRaw('DATE_FORMAT(date, "%Y-%m") = ?', [$period])
      ->groupBy('client_id', DB::raw("DATE_FORMAT(date, '%Y-%m')"))
      ->first();

    $totalForm44 = $totalForm44 ?: (object)['increase_vat' => 0];

    // ----- TOTAL FORM 4.5 -----
    $totalForm45 = $this->form45
      ->selectRaw("
      client_id,
      DATE_FORMAT(date, '%Y-%m') AS tax_period,
      ROUND(COALESCE(SUM(net_increase), 0), 2) AS increase_vat
    ")
      ->where('client_id', $client_id)
      ->whereRaw('DATE_FORMAT(date, "%Y-%m") = ?', [$period])
      ->groupBy('client_id', DB::raw("DATE_FORMAT(date, '%Y-%m')"))
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
        ->whereRaw('DATE_FORMAT(entry_date, "%Y-%m") = ?', [$period])
        ->where('mushaknote_id', $mushak22->id)
        ->groupBy('client_id')
        ->first();

      $note22Sale = $this->sale
        ->where('client_id', $client_id)
        ->whereRaw('DATE_FORMAT(date, "%Y-%m") = ?', [$period])
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
      // Get the LATEST closing balance whose tax_period is <= prevPeriod
      // For first month (July) this will pick June  (your 664.18)
      // For August it will pick July (saved by system), etc.
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

    // 🔹 CURRENT MONTH CLOSING (your exact logic)
    // First month:
    //   prevNetPayable = 664.18 (June)
    //   closing_current = -netPayableRegular + 664.18
    $closignBalanceCurrentRegular    = round(-$netPayableRegular + $prevNetPayable, 2);
    $closignBalanceCurrentSettlement = round(optional($treasury)->amount - $netPayableSettlement, 2);

    // 🔹 Auto-save current month closing so next month can use it
    if ($closingId) {
      $this->adjustment->updateOrCreate(
        [
          'client_id'  => $client_id,
          'expense_id' => $closingId,
          'tax_period' => $period, // current month
        ],
        [
          'amount'     => $closignBalanceCurrentRegular,
        ]
      );
    }

    // ----- TOTAL PURCHASE QUANTITY -----
    $totalPurchaseQuantity = $this->purchase
      ->where('client_id', $client_id)
      ->whereRaw('DATE_FORMAT(entry_date, "%Y-%m") = ?', [$period])
      ->sum('quantity');

    $totalPurchaseQuantity = round($totalPurchaseQuantity ?? 0, 2);

    // ----- TOTAL BOE COUNT -----
    $totalBoe = $this->purchase
      ->where('client_id', $client_id)
      ->whereRaw('DATE_FORMAT(entry_date, "%Y-%m") = ?', [$period])
      ->count('boe');

    // ----- TOTAL MCU SUM -----
    $totalMcu = $this->purchase
      ->where('client_id', $client_id)
      ->whereRaw('DATE_FORMAT(entry_date, "%Y-%m") = ?', [$period])
      ->sum('mcu_amount');

    $totalMcu = round($totalMcu ?? 0, 2);

    // ----- PURCHASE DETAILS -----
    $purchaseDetails = $this->purchase
      ->with(['item', 'mushaknote', 'unit', 'stationcode'])
      ->where('client_id', $client_id)
      ->whereRaw('DATE_FORMAT(entry_date, "%Y-%m") = ?', [$period])
      ->get();

    return Inertia::render('report/summary/index', [
      'summary' => [
        'client'                         => $client,
        'sale'                           => $sale,
        'purchase'                       => $purchase,
        'saleSum'                        => $saleSum,
        'purchaseSum'                    => $purchaseSum,
        'adjustment'                     => $adjustment,
        'closignBalance'                 => $closignBalance,                   // previous closing (June for first month)
        'treasury'                       => $treasury,
        'netPayableSettlement'           => $netPayableSettlement,
        'netPayableRegular'              => $netPayableRegular,
        'prevNetPayable'                 => $prevNetPayable,
        'closignBalanceCurrentRegular'   => $closignBalanceCurrentRegular,
        'closignBalanceCurrentSettlement' => $closignBalanceCurrentSettlement,
        'totalPurchaseQuantity'          => $totalPurchaseQuantity,
        'totalBoe'                       => $totalBoe,
        'totalMcu'                       => $totalMcu,
        'note22Purchase'                 => $note22Purchase,
        'note22Sale'                     => $note22Sale,
        'totalAtValue'                   => $totalAtValue,
        'purchaseDetails'                => $purchaseDetails,
        'totalCreditnote'                => $totalCreditnote,
        'totalDebitnote'                 => $totalDebitnote,
        'totalForm44'                    => $totalForm44,
        'totalForm45'                    => $totalForm45,
        'totalIncAdj'                    => $totalIncAdj,
        'totalDecAdj'                    => $totalDecAdj,
        'totalIncreasing'                => $totalIncreasing,
        'totalDecreasing'                => $totalDecreasing,
        'note34'                         => $note34,
        'note35'                         => $note35,
        'note36'                         => $note36,
      ],
    ]);
  }

  /**
   * Get CLOSING BALANCE (Regular) for any given period (YYYY-mm) for a client.
   *
   * Logic:
   *   If this period has a saved "Closing Balance" adjustment → return that.
   *   Else:
   *     Closing(period) = -NetPayable(period) + Closing(prevPeriod)
   *   (and prevPeriod closing is also resolved recursively)
   */
  protected function calculateNetPayableForPeriod(int $client_id, string $period, $expenseIds, int $depth = 0): float
  {
    // safety to avoid infinite recursion
    if ($depth > 36) {
      return 0.0;
    }

    $closingId = $expenseIds['Closing Balance'] ?? null;

    // 1) BASE CASE: if this period already has a stored Closing Balance, use it.
    if ($closingId) {
      $existing = $this->adjustment
        ->selectRaw("ROUND(COALESCE(SUM(amount), 0), 2) AS amount")
        ->where('client_id', $client_id)
        ->where('expense_id', $closingId)
        ->where('tax_period', $period)
        ->first();

      if ($existing && $existing->amount != 0.0) {
        return (float) $existing->amount;
      }
    }

    // 2) Recursively get previous period closing
    $prevPeriod = date('Y-m', strtotime($period . '-01 -1 month'));
    $prevClosing = 0.0;

    if ($prevPeriod) {
      $prevClosing = $this->calculateNetPayableForPeriod($client_id, $prevPeriod, $expenseIds, $depth + 1);
    }

    // 3) Now calculate NET PAYABLE for THIS period (without subtracting previous closing here)

    // SALE VAT
    $saleSum = $this->sale
      ->where('client_id', $client_id)
      ->whereRaw('DATE_FORMAT(date, "%Y-%m") = ?', [$period])
      ->selectRaw("ROUND(COALESCE(SUM(vat_value), 0), 2) AS vat_value_sum")
      ->first();

    $salesVatValue = optional($saleSum)->vat_value_sum ?? 0;

    // NOTE 15 VAT
    $mushak15  = $this->mushaknote->where('name_note_no', 'note_15')->first();
    $note15Vat = 0;

    if ($mushak15) {
      $note15 = $this->purchase
        ->selectRaw("client_id, ROUND(COALESCE(SUM(vat_value), 0), 2) AS vat_value")
        ->where('client_id', $client_id)
        ->whereRaw('DATE_FORMAT(entry_date, "%Y-%m") = ?', [$period])
        ->where('mushaknote_id', $mushak15->id)
        ->groupBy('client_id')
        ->first();

      $note15Vat = optional($note15)->vat_value ?? 0;
    }

    // TOTAL AT
    $totalAtValue = $this->purchase
      ->where('client_id', $client_id)
      ->whereRaw('DATE_FORMAT(entry_date, "%Y-%m") = ?', [$period])
      ->sum('at_value');

    $totalAtValue = round($totalAtValue ?? 0, 2);

    // TREASURY
    $treasury = $this->treasury
      ->selectRaw("client_id, branch_id, tax_period, ROUND(COALESCE(SUM(amount), 0), 2) AS amount")
      ->where('client_id', $client_id)
      ->where('tax_period', $period)
      ->groupBy('client_id', 'branch_id', 'tax_period')
      ->first();

    $treas = optional($treasury)->amount ?? 0;

    // INCREASE / DECREASE / PENALTY
    $increasingId = $expenseIds['Increase'] ?? null;
    $decreasingId = $expenseIds['Decrease'] ?? null;
    $penaltyId    = $expenseIds['Penalty'] ?? null;

    $inc = $increasingId ? optional(
      $this->adjustment
        ->selectRaw("client_id, expense_id, tax_period, ROUND(COALESCE(SUM(amount), 0), 2) AS amount")
        ->where('client_id', $client_id)
        ->where('tax_period', $period)
        ->where('expense_id', $increasingId)
        ->groupBy('client_id', 'expense_id', 'tax_period')
        ->first()
    )->amount ?? 0 : 0;

    $dec = $decreasingId ? optional(
      $this->adjustment
        ->selectRaw("client_id, expense_id, tax_period, ROUND(COALESCE(SUM(amount), 0), 2) AS amount")
        ->where('client_id', $client_id)
        ->where('tax_period', $period)
        ->where('expense_id', $decreasingId)
        ->groupBy('client_id', 'expense_id', 'tax_period')
        ->first()
    )->amount ?? 0 : 0;

    $pen = $penaltyId ? optional(
      $this->adjustment
        ->selectRaw("client_id, expense_id, tax_period, ROUND(COALESCE(SUM(amount), 0), 2) AS amount")
        ->where('client_id', $client_id)
        ->where('tax_period', $period)
        ->where('expense_id', $penaltyId)
        ->groupBy('client_id', 'expense_id', 'tax_period')
        ->first()
    )->amount ?? 0 : 0;

    // CREDIT NOTE (value type, not VAT only)
    $crn = optional(
      $this->creditnote
        ->selectRaw("
                client_id,
                DATE_FORMAT(issued_date, '%Y-%m') AS tax_period,
                ROUND(COALESCE(SUM(value_of_decreasing_adjustment), 0), 2) AS value
            ")
        ->where('client_id', $client_id)
        ->whereRaw('DATE_FORMAT(issued_date, "%Y-%m") = ?', [$period])
        ->groupBy('client_id', DB::raw("DATE_FORMAT(issued_date, '%Y-%m')"))
        ->first()
    )->value ?? 0;

    // DEBIT NOTE (value type)
    $dbn = optional(
      $this->debitnote
        ->selectRaw("
                client_id,
                DATE_FORMAT(issued_date, '%Y-%m') AS tax_period,
                ROUND(COALESCE(SUM(value_of_increasing_adjustment), 0), 2) AS value
            ")
        ->where('client_id', $client_id)
        ->whereRaw('DATE_FORMAT(issued_date, "%Y-%m") = ?', [$period])
        ->groupBy('client_id', DB::raw("DATE_FORMAT(issued_date, '%Y-%m')"))
        ->first()
    )->value ?? 0;

    // FORM 4.4 (value)
    $f44 = optional(
      $this->form44
        ->selectRaw("
                client_id,
                DATE_FORMAT(date, '%Y-%m') AS tax_period,
                ROUND(COALESCE(SUM(value), 0), 2) AS value
            ")
        ->where('client_id', $client_id)
        ->whereRaw('DATE_FORMAT(date, "%Y-%m") = ?', [$period])
        ->groupBy('client_id', DB::raw("DATE_FORMAT(date, '%Y-%m')"))
        ->first()
    )->value ?? 0;

    // FORM 4.5 (value)
    $f45 = optional(
      $this->form45
        ->selectRaw("
                client_id,
                DATE_FORMAT(date, '%Y-%m') AS tax_period,
                ROUND(COALESCE(SUM(value), 0), 2) AS value
            ")
        ->where('client_id', $client_id)
        ->whereRaw('DATE_FORMAT(date, "%Y-%m") = ?', [$period])
        ->groupBy('client_id', DB::raw("DATE_FORMAT(date, '%Y-%m')"))
        ->first()
    )->value ?? 0;

    // 4) Net payable for THIS period (same shape as your regular formula, without previous closing)
    $netForPeriod = $salesVatValue - $note15Vat + $inc - $dec + $pen - $totalAtValue - $treas - $crn + $dbn + $f44 + $f45;

    // 5) FINAL: Closing(period) = -Net(period) + Closing(prevPeriod)
    $closingCurrent = -$netForPeriod + $prevClosing;

    return round($closingCurrent, 2);
  }

  /**
   * This is for 4.3 pdf shows
   */
  public function pdf(Request $request)
  {
    $user    = Auth::user();
    $isAdmin = in_array(optional($user->role)->name, ['admin']);
    $client_id = $request->query('client_id');
    $monthYear = $request->query('monthYear');

    // --------- BLOCK IF CLIENT NOT BELONGS TO USER ----------
    $client = $this->client->findOrFail($client_id);

    if (!$isAdmin && $client->user_id !== $user->id) {
      abort(403, 'Unauthorised'); // <-- hard block, no data
    }

    $period     = date('Y-m', strtotime($monthYear));
    $prevPeriod = date('Y-m', strtotime($period . '-01 -1 month'));

    // ----- SALE (grouped) -----
    $sale = $this->sale
      ->selectRaw("
      item_id,
      unit_id,
      sales.vat as vat_rate,
      ROUND(COALESCE(SUM(quantity), 0), 2)      AS quantity,
      ROUND(COALESCE(SUM(item_price), 0), 2)    AS item_price,
      ROUND(COALESCE(SUM(sd_value), 0), 2)      AS sd_value,
      ROUND(COALESCE(SUM(vat_value), 0), 2)     AS vat_value,
      DATE_FORMAT(date, '%Y-%m') AS tax_period
    ")
      ->leftJoin('items as item', 'item_id', '=', 'item.id')
      ->with(['item', 'unit'])
      ->where('client_id', $client_id)
      ->whereRaw('DATE_FORMAT(date, "%Y-%m") = ?', [$period])
      ->groupBy('vat_rate', 'item.hs_code')
      ->get();

    // ----- PURCHASE (grouped) -----
    $purchase = $this->purchase
      ->selectRaw("
      item_id,
      mushaknote_id,
      unit_id,
      purchases.vat as vat_rate,
      ROUND(COALESCE(SUM(total_quantity), 0), 2)   AS total_quantity,
      ROUND(COALESCE(SUM(assessable_value), 0), 2) AS assessable_value,
      ROUND(COALESCE(SUM(base_value), 0), 2)       AS base_value,
      ROUND(COALESCE(SUM(sd_value), 0), 2)         AS sd_value,
      ROUND(COALESCE(SUM(vat_value), 0), 2)        AS vat_value,
      ROUND(COALESCE(SUM(at_value), 0), 2)         AS at_value,
      DATE_FORMAT(entry_date, '%Y-%m') AS tax_period
    ")
      ->leftJoin('items as item', 'item_id', '=', 'item.id')
      ->leftJoin('mushaknotes as mushaknote', 'mushaknote_id', '=', 'mushaknote.id')
      ->with(['item', 'mushaknote', 'unit'])
      ->where('client_id', $client_id)
      ->whereRaw('DATE_FORMAT(entry_date, "%Y-%m") = ?', [$period])
      ->groupBy('mushaknote.name_note_no', 'item.hs_code')
      ->get();

    // ----- SALE SUM -----
    $saleSum = $this->sale
      ->where('client_id', $client_id)
      ->whereRaw('DATE_FORMAT(date, "%Y-%m") = ?', [$period])
      ->selectRaw("
      ROUND(COALESCE(SUM(item_price), 0), 2) AS item_price_sum,
      ROUND(COALESCE(SUM(sd_value), 0), 2)   AS sd_value_sum,
      ROUND(COALESCE(SUM(vat_value), 0), 2)  AS vat_value_sum
    ")
      ->first();

    // ----- PURCHASE SUM -----
    $purchaseSum = $this->purchase
      ->where('client_id', $client_id)
      ->whereRaw('DATE_FORMAT(entry_date, "%Y-%m") = ?', [$period])
      ->selectRaw("
      ROUND(COALESCE(SUM(assessable_value), 0), 2) AS assessable_value_sum,
      ROUND(COALESCE(SUM(base_value), 0), 2)       AS base_value_sum,
      ROUND(COALESCE(SUM(sd_value), 0), 2)         AS sd_value_sum,
      ROUND(COALESCE(SUM(vat_value), 0), 2)        AS vat_value_sum,
      ROUND(COALESCE(SUM(at_value), 0), 2)         AS at_value_sum
    ")
      ->first();

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
        ->whereRaw('DATE_FORMAT(entry_date, "%Y-%m") = ?', [$period])
        ->where('mushaknote_id', $mushak15->id)
        ->groupBy('client_id')
        ->first();
    }

    $note15Vat = optional($note15)->vat_value ?? 0;

    // ----- TOTAL AT -----
    $totalAtValue = $this->purchase
      ->where('client_id', $client_id)
      ->whereRaw('DATE_FORMAT(entry_date, "%Y-%m") = ?', [$period])
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
      DATE_FORMAT(issued_date, '%Y-%m') AS tax_period,
      ROUND(COALESCE(SUM(vat_of_decreasing_adjustment), 0), 2) AS decrease_vat,
      ROUND(COALESCE(SUM(sd_of_decreasing_adjustment), 0), 2) AS decrease_sd
    ")
      ->where('client_id', $client_id)
      ->whereRaw('DATE_FORMAT(issued_date, "%Y-%m") = ?', [$period])
      ->groupBy('client_id', DB::raw("DATE_FORMAT(issued_date, '%Y-%m')"))
      ->first();

    $totalCreditnote = $totalCreditnote ?: (object)['decrease_vat' => 0, 'decrease_sd' => 0];

    // ----- TOTAL DEBITNOTE -----
    $totalDebitnote = $this->debitnote
      ->selectRaw("
      client_id,
      DATE_FORMAT(issued_date, '%Y-%m') AS tax_period,
      ROUND(COALESCE(SUM(vat_of_increasing_adjustment), 0), 2) AS increase_vat,
      ROUND(COALESCE(SUM(sd_of_increasing_adjustment), 0), 2) AS increase_sd
    ")
      ->where('client_id', $client_id)
      ->whereRaw('DATE_FORMAT(issued_date, "%Y-%m") = ?', [$period])
      ->groupBy('client_id', DB::raw("DATE_FORMAT(issued_date, '%Y-%m')"))
      ->first();

    $totalDebitnote = $totalDebitnote ?: (object)['increase_vat' => 0];

    // ----- TOTAL FORM 4.4 -----
    $totalForm44 = $this->form44
      ->selectRaw("
      client_id,
      DATE_FORMAT(date, '%Y-%m') AS tax_period,
      ROUND(COALESCE(SUM(net_increase), 0), 2) AS increase_vat
    ")
      ->where('client_id', $client_id)
      ->whereRaw('DATE_FORMAT(date, "%Y-%m") = ?', [$period])
      ->groupBy('client_id', DB::raw("DATE_FORMAT(date, '%Y-%m')"))
      ->first();

    $totalForm44 = $totalForm44 ?: (object)['increase_vat' => 0];

    // ----- TOTAL FORM 4.5 -----
    $totalForm45 = $this->form45
      ->selectRaw("
      client_id,
      DATE_FORMAT(date, '%Y-%m') AS tax_period,
      ROUND(COALESCE(SUM(net_increase), 0), 2) AS increase_vat
    ")
      ->where('client_id', $client_id)
      ->whereRaw('DATE_FORMAT(date, "%Y-%m") = ?', [$period])
      ->groupBy('client_id', DB::raw("DATE_FORMAT(date, '%Y-%m')"))
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
        ->whereRaw('DATE_FORMAT(entry_date, "%Y-%m") = ?', [$period])
        ->where('mushaknote_id', $mushak22->id)
        ->groupBy('client_id')
        ->first();

      $note22Sale = $this->sale
        ->where('client_id', $client_id)
        ->whereRaw('DATE_FORMAT(date, "%Y-%m") = ?', [$period])
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
      // Get the LATEST closing balance whose tax_period is <= prevPeriod
      // For first month (July) this will pick June  (your 664.18)
      // For August it will pick July (saved by system), etc.
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

    // 🔹 CURRENT MONTH CLOSING (your exact logic)
    // First month:
    //   prevNetPayable = 664.18 (June)
    //   closing_current = -netPayableRegular + 664.18
    $closignBalanceCurrentRegular    = round(-$netPayableRegular + $prevNetPayable, 2);
    $closignBalanceCurrentSettlement = round(optional($treasury)->amount - $netPayableSettlement, 2);

    // 🔹 Auto-save current month closing so next month can use it
    if ($closingId) {
      $this->adjustment->updateOrCreate(
        [
          'client_id'  => $client_id,
          'expense_id' => $closingId,
          'tax_period' => $period, // current month
        ],
        [
          'amount'     => $closignBalanceCurrentRegular,
        ]
      );
    }

    // ----- TOTAL PURCHASE QUANTITY -----
    $totalPurchaseQuantity = $this->purchase
      ->where('client_id', $client_id)
      ->whereRaw('DATE_FORMAT(entry_date, "%Y-%m") = ?', [$period])
      ->sum('quantity');

    $totalPurchaseQuantity = round($totalPurchaseQuantity ?? 0, 2);

    // ----- TOTAL BOE COUNT -----
    $totalBoe = $this->purchase
      ->where('client_id', $client_id)
      ->whereRaw('DATE_FORMAT(entry_date, "%Y-%m") = ?', [$period])
      ->count('boe');

    // ----- TOTAL MCU SUM -----
    $totalMcu = $this->purchase
      ->where('client_id', $client_id)
      ->whereRaw('DATE_FORMAT(entry_date, "%Y-%m") = ?', [$period])
      ->sum('mcu_amount');

    $totalMcu = round($totalMcu ?? 0, 2);

    // ----- PURCHASE DETAILS -----
    $purchaseDetails = $this->purchase
      ->with(['item', 'mushaknote', 'unit', 'stationcode'])
      ->where('client_id', $client_id)
      ->whereRaw('DATE_FORMAT(entry_date, "%Y-%m") = ?', [$period])
      ->get();

    $html = view('pdf.report-summary', [
      'summary' => [
        'client'                         => $client,
        'sale'                           => $sale,
        'purchase'                       => $purchase,
        'saleSum'                        => $saleSum,
        'purchaseSum'                    => $purchaseSum,
        'adjustment'                     => $adjustment,
        'closignBalance'                 => $closignBalance,                   // previous closing (June for first month)
        'treasury'                       => $treasury,
        'netPayableSettlement'           => $netPayableSettlement,
        'netPayableRegular'              => $netPayableRegular,
        'prevNetPayable'                 => $prevNetPayable,
        'closignBalanceCurrentRegular'   => $closignBalanceCurrentRegular,
        'closignBalanceCurrentSettlement' => $closignBalanceCurrentSettlement,
        'totalPurchaseQuantity'          => $totalPurchaseQuantity,
        'totalBoe'                       => $totalBoe,
        'totalMcu'                       => $totalMcu,
        'note22Purchase'                 => $note22Purchase,
        'note22Sale'                     => $note22Sale,
        'totalAtValue'                   => $totalAtValue,
        'purchaseDetails'                => $purchaseDetails,
        'totalCreditnote'                => $totalCreditnote,
        'totalDebitnote'                 => $totalDebitnote,
        'totalForm44'                    => $totalForm44,
        'totalForm45'                    => $totalForm45,
        'totalIncAdj'                    => $totalIncAdj,
        'totalDecAdj'                    => $totalDecAdj,
        'totalIncreasing'                => $totalIncreasing,
        'totalDecreasing'                => $totalDecreasing,
        'note34'                         => $note34,
        'note35'                         => $note35,
        'note36'                         => $note36,
      ],
    ])->render();

    $browser = Browsershot::html($html)
      ->showBackground()
      ->margins(10, 10, 10, 10)
      ->format('A4')
      ->landscape()
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
    $fileName = "filename=report-summary-{$client->bin}" . Carbon::parse($monthYear)->format('M-Y');

    return response($pdfData)
      ->header('Content-Type', 'application/pdf')
      ->header('Content-Disposition', "inline; $fileName");
  }
}
