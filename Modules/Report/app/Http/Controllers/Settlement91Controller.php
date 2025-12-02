<?php

namespace Modules\Report\Http\Controllers;

use App\Http\Controllers\Controller;
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

class Settlement91Controller extends Controller
{
  public $client;
  public $purchase;
  public $sale;
  public $mushaknote;
  public $expense;
  public $adjustment;
  public $treasury;
  public $creditnote;
  public $debitnote;
  public $form44;
  public $form45;
  public function __construct(Client $client, Purchase $purchase, Sale $sale, Mushaknote $mushaknote, Expense $expense, Adjustment $adjustment, Treasury $treasury, Creditnote $creditnote, Debitnote $debitnote, Form44 $form44, Form45 $form45)
  {
    $this->client = $client;
    $this->purchase = $purchase;
    $this->sale = $sale;
    $this->mushaknote = $mushaknote;
    $this->expense = $expense;
    $this->adjustment = $adjustment;
    $this->treasury = $treasury;
    $this->creditnote = $creditnote;
    $this->debitnote = $debitnote;
    $this->form44 = $form44;
    $this->form45 = $form45;
  }

  public function index(Request $request, $client_id, $monthYear)
  {
    $user    = Auth::user();
    $isAdmin = in_array(optional($user->role)->name, ['admin']);

    // 1) Authorise client access
    $client = $this->client->findOrFail($client_id);
    if (!$isAdmin && $client->user_id !== $user->id) {
      abort(403, 'Unauthorised');
    }

    // 2) Periods: current + previous month
    $period     = date('Y-m', strtotime($monthYear));
    $prevPeriod = date('Y-m', strtotime("$period-01 -1 month"));

    // 3) Expenses Ids
    $expenseIds = $this->expense->whereIn('name', ['Closing Balance', 'Increase', 'Decrease', 'Penalty'])->pluck('id', 'name');

    /*
    |--------------------------------------------------------------------------
    | HELPER CLOSURES
    |--------------------------------------------------------------------------
    */

    // Get sale summary for a given VAT rate in this period
    $saleSummaryByVatRate = function ($vatRate) use ($client_id, $period) {
      if (!$vatRate) return null;

      return $this->sale
        ->selectRaw("
          ROUND(COALESCE(SUM(total_price), 0), 2) AS total_price,
          ROUND(COALESCE(SUM(sd_value), 0), 2)    AS sd_value,
          ROUND(COALESCE(SUM(vat_value), 0), 2)   AS vat_value
        ")
        ->where('client_id', $client_id)
        ->where('vat', $vatRate)
        ->whereRaw('DATE_FORMAT(date, "%Y-%m") = ?', [$period])
        ->first();
    };

    // Get importer purchase summary for a given Mushak note id in this period
    $purchaseSummaryByNote = function ($mushaknoteId) use ($client_id, $period) {
      if (!$mushaknoteId) return null;

      return $this->purchase
        ->selectRaw("
          ROUND(COALESCE(SUM(base_value), 0), 2) AS base_value,
          ROUND(COALESCE(SUM(vat_value), 0), 2)  AS vat_value
        ")
        ->where('client_id', $client_id)
        ->where('mushaknote_id', $mushaknoteId)
        ->whereRaw('DATE_FORMAT(entry_date, "%Y-%m") = ?', [$period])
        ->first();
    };

    // Get adjustment sum for an expense name in this period
    $adjustmentByExpenseName = function (string $name) use ($client_id, $period) {
      $expense = $this->expense->where('name', $name)->first();
      if (!$expense) return null;

      return $this->adjustment
        ->selectRaw("
          client_id,
          expense_id,
          tax_period,
          ROUND(COALESCE(SUM(amount), 0), 2) AS amount
        ")
        ->leftJoin('expenses as expense', 'expense_id', '=', 'expense.id')
        ->where('client_id', $client_id)
        ->where('tax_period', $period)
        ->where('expense_id', $expense->id)
        ->groupBy('client_id', 'expense_id', 'tax_period')
        ->first();
    };

    /*
    |--------------------------------------------------------------------------
    | SALES BY VAT RATE (0 / 7.5 / 15 via note_13 / note_15 / note_22)
    |--------------------------------------------------------------------------
    */

    $notePercents = $this->mushaknote->whereIn('name_note_no', ['note_13', 'note_15', 'note_22'])->pluck('percent', 'name_note_no');

    $sale13 = $saleSummaryByVatRate($notePercents['note_13'] ?? null);
    $sale15 = $saleSummaryByVatRate($notePercents['note_15'] ?? null);
    $sale22 = $saleSummaryByVatRate($notePercents['note_22'] ?? null);

    $getSaleField = fn($row, $field) => $row->{$field} ?? 0;

    // Map to your existing variable names (0 / 7.5 / 15)
    $totalSaleValueSum13  = $getSaleField($sale13, 'total_price');
    $totalSaleValueSum15  = $getSaleField($sale15, 'total_price');
    $totalSaleValueSum22  = $getSaleField($sale22, 'total_price');

    $totalSaleSdSum13  = $getSaleField($sale13, 'sd_value');
    $totalSaleSdSum15  = $getSaleField($sale15, 'sd_value');
    $totalSaleSdSum22  = $getSaleField($sale22, 'sd_value');

    $totalSaleVatSum13  = $getSaleField($sale13, 'vat_value');
    $totalSaleVatSum15  = $getSaleField($sale15, 'vat_value');
    $totalSaleVatSum22  = $getSaleField($sale22, 'vat_value');

    $totalValue = $totalSaleValueSum13 + $totalSaleValueSum15 + $totalSaleValueSum22;
    $totalSd    = $totalSaleSdSum13 + $totalSaleSdSum15 + $totalSaleSdSum22;
    $totalVat   = $totalSaleVatSum13 + $totalSaleVatSum15 + $totalSaleVatSum22;

    /*
    |--------------------------------------------------------------------------
    | IMPORT PURCHASES BY NOTE (13 / 15 / 22)
    |--------------------------------------------------------------------------
    */

    $note13 = $this->mushaknote->where('name_note_no', 'note_13')->first();
    $note15 = $this->mushaknote->where('name_note_no', 'note_15')->first();
    $note22 = $this->mushaknote->where('name_note_no', 'note_22')->first();

    $purchase13 = $note13 ? $purchaseSummaryByNote($note13->id) : null;
    $purchase15 = $note15 ? $purchaseSummaryByNote($note15->id) : null;
    $purchase22 = $note22 ? $purchaseSummaryByNote($note22->id) : null;

    /*
    |--------------------------------------------------------------------------
    | LOCAL PURCHASES (you already had these somewhere above – keeping names)
    |--------------------------------------------------------------------------
    | Assuming $localpurchase12, $localpurchase14, $localpurchase21 are already
    | calculated earlier in your method. If not, you can plug them in here.
    */

    $totalPurchaseValue = (!empty($purchase13) ? $purchase13->base_value : 0) + (!empty($purchase15) ? $purchase15->base_value : 0) + (!empty($purchase22) ? $purchase22->base_value : 0);

    $totalPurchaseVat = (!empty($purchase15) ? $purchase15->vat : 0);

    /*
    |--------------------------------------------------------------------------
    | ADJUSTMENTS: Increase / Decrease / AT / Penalty / Closing / Treasury
    |--------------------------------------------------------------------------
    */

    $totalIncreasing  = $adjustmentByExpenseName('Increase');
    $totalDecreasing  = $adjustmentByExpenseName('Decrease');
    $totalPenalty     = $adjustmentByExpenseName('Penalty');

    // AT from importer purchase
    $totalAtValue = $this->purchase
      ->selectRaw("
        client_id,
        ROUND(COALESCE(SUM(at_value), 0), 2) AS at_value
      ")
      ->where('client_id', $client_id)
      ->whereRaw('DATE_FORMAT(entry_date, "%Y-%m") = ?', [$period])
      ->groupBy('client_id')
      ->first();

    $totalAtValue = optional($totalAtValue)->value ?? 0;

    // ----- NOTE 22 VAT -----
    $mushak22 = $this->mushaknote->where('name_note_no', 'note_22')->first();
    $note22   = null;

    if ($mushak22) {
      $note22 = $this->purchase
        ->selectRaw("
          client_id,
          ROUND(COALESCE(SUM(vat_value), 0), 2) AS vat_value,
          ROUND(COALESCE(SUM(at_value), 0), 2) AS at_value
        ")
        ->where('client_id', $client_id)
        ->whereRaw('DATE_FORMAT(entry_date, "%Y-%m") = ?', [$period])
        ->where('mushaknote_id', $mushak22->id)
        ->groupBy('client_id')
        ->first();
    }

    $note22Vat = optional($note22)->vat_value ?? 0;
    $note22At = optional($note22)->at_value ?? 0;

    $totalDecreasingAt = $totalAtValue + optional($totalDecreasing)->amount;

    // Treasury for this period
    $treasury = $this->treasury
      ->selectRaw("
        client_id,
        branch_id,
        tax_period,
        ROUND(COALESCE(SUM(amount), 0), 2) AS amount
      ")
      ->with(['branch', 'branch.bank', 'branch.division'])
      ->where('client_id', $client_id)
      ->where('tax_period', $period)
      ->groupBy('client_id', 'branch_id', 'tax_period')
      ->first();

    // ----- TOTAL CREDITNOTE -----
    $totalCreditnote = $this->creditnote
      ->selectRaw("
        client_id,
        DATE_FORMAT(issued_date, '%Y-%m') AS tax_period,
        ROUND(COALESCE(SUM(value_of_decreasing_adjustment), 0), 2) AS value
      ")->where('client_id', $client_id)
      ->whereRaw("DATE_FORMAT(issued_date, '%Y-%m') = ?", [$period])
      ->groupBy('client_id', DB::raw("DATE_FORMAT(issued_date, '%Y-%m')"))
      ->first();

    $totalCreditnote = $totalCreditnote ?: (object)['value' => 0];

    // ----- TOTAL DEBITNOTE -----
    $totalDebitnote = $this->debitnote
      ->selectRaw("
        client_id,
        DATE_FORMAT(issued_date, '%Y-%m') AS tax_period,
        ROUND(COALESCE(SUM(value_of_increasing_adjustment), 0), 2) AS value
      ")->where('client_id', $client_id)
      ->whereRaw('DATE_FORMAT(issued_date, "%Y-%m") = ?', [$period])
      ->groupBy('client_id', DB::raw("DATE_FORMAT(issued_date, '%Y-%m')"))
      ->first();

    $totalDebitnote = $totalDebitnote ?: (object)['value' => 0];

    // ----- TOTAL FORM 4.4 -----
    $totalForm44 = $this->form44
      ->selectRaw("
        client_id,
        DATE_FORMAT(date, '%Y-%m') AS tax_period,
        ROUND(COALESCE(SUM(value), 0), 2) AS value
      ")->where('client_id', $client_id)
      ->whereRaw('DATE_FORMAT(date, "%Y-%m") = ?', [$period])
      ->groupBy('client_id', DB::raw("DATE_FORMAT(date, '%Y-%m')"))
      ->first();

    $totalForm44 = $totalForm44 ?: (object)['value' => 0];

    // ----- TOTAL FORM 4.5 -----
    $totalForm45 = $this->form45
      ->selectRaw("
        client_id,
        DATE_FORMAT(date, '%Y-%m') AS tax_period,
        ROUND(COALESCE(SUM(value), 0), 2) AS value
      ")->where('client_id', $client_id)
      ->whereRaw('DATE_FORMAT(date, "%Y-%m") = ?', [$period])
      ->groupBy('client_id', DB::raw("DATE_FORMAT(date, '%Y-%m')"))
      ->first();

    $totalForm45 = $totalForm45 ?: (object)['value' => 0];

    // Closing balance (previous tax period)
    $closing = $this->expense->where('name', 'Closing Balance')->first();
    $closignBalance = null;

    if ($closing) {
      $closignBalance = $this->adjustment
        ->selectRaw("
          client_id,
          expense_id,
          tax_period,
          ROUND(COALESCE(SUM(amount), 0), 2) AS amount
        ")
        ->leftJoin('expenses as expense', 'expense_id', '=', 'expense.id')
        ->with(['expense'])
        ->where('expense_id', $closing->id)
        ->where('client_id', $client_id)
        ->where('tax_period', $prevPeriod)
        ->groupBy('client_id', 'expense_id', 'tax_period')
        ->first();
    }

    /*
    |--------------------------------------------------------------------------
    | NET PAYABLE & NOTE 34
    |--------------------------------------------------------------------------
    |
    | Net Payable VAT:
    | Total Sales VAT (local+import)
    | – (Total Purchase VAT)
    | + Total Increasing Adjustment
    | – Total Decreasing Adjustment + Penalty
    | – Total AT – Closing Balance of Last Tax Period – Treasury
    */

    if (!$client_id || !$monthYear) {
      $netPayable = 0;
    } else {
      $vatTotal          = $totalVat;
      $purchaseVat       = $totalPurchaseVat;
      $incAmount         = optional($totalIncreasing)->amount ?? 0;
      $decAmountWithAt   = $totalDecreasingAt;
      $penAmount         = optional($totalPenalty)->amount ?? 0;
      $treasuryAmount    = optional($treasury)->amount ?? 0;
      $creditnoteAmount  = optional($totalCreditnote)->value ?? 0;
      $debitnoteAmount   = optional($totalDebitnote)->value ?? 0;
      $form44Amount      = optional($totalForm44)->value ?? 0;
      $form45Amount      = optional($totalForm45)->value ?? 0;

      $netPayable = round($vatTotal - $purchaseVat + $incAmount - $decAmountWithAt + $penAmount - $treasuryAmount - $creditnoteAmount + $debitnoteAmount + $form44Amount + $form45Amount, 2);

      // 🔹 CONDITIONAL PREVIOUS MONTH NET
      $prevClosingAmount = optional($closignBalance)->amount;

      if (!is_null($prevClosingAmount) && $prevClosingAmount != 0) {
        // if we already have closing for previous month, use that
        $prevNetPayable = $prevClosingAmount;
      } else {
        // first month (or no closing) → fallback to full recalculation
        $prevNetPayable = $this->calculateNetPayableForPeriod($client_id, $prevPeriod, $expenseIds);
      }

      // 🔹 CURRENT MONTH CLOSING (to show/use)
      $closignBalanceCurrent = round($netPayable - $prevNetPayable, 2);

      // note34 = without closing & treasury
      $note34 = $vatTotal - $purchaseVat + $incAmount - $decAmountWithAt;
    }
    /*
    |--------------------------------------------------------------------------
    | RESPONSE
    |--------------------------------------------------------------------------
    */
    return Inertia::render('report/settlement91/index', [
      'settlement91' => [
        'client' => $client,

        'totalSaleValueSum13'   => number_format($totalSaleValueSum13, 2, '.', ''),
        'totalSaleValueSum15'   => number_format($totalSaleValueSum15, 2, '.', ''),
        'totalSaleValueSum22'   => number_format($totalSaleValueSum22, 2, '.', ''),

        'totalSaleSdSum13'      => number_format($totalSaleSdSum13, 2, '.', ''),
        'totalSaleSdSum15'      => number_format($totalSaleSdSum15, 2, '.', ''),
        'totalSaleSdSum22'      => number_format($totalSaleSdSum22, 2, '.', ''),

        'totalSaleVatSum13'     => number_format($totalSaleVatSum13, 2, '.', ''),
        'totalSaleVatSum15'     => number_format($totalSaleVatSum15, 2, '.', ''),
        'totalSaleVatSum22'     => number_format($totalSaleVatSum22, 2, '.', ''),

        'totalValue'            => number_format($totalValue, 2, '.', ''),
        'totalSd'               => number_format($totalSd, 2, '.', ''),
        'totalVat'              => number_format($totalVat, 2, '.', ''),

        'purchase13'            => $purchase13,
        'purchase15'            => $purchase15,
        'purchase22'            => $purchase22,

        'totalCreditnote'       => $totalCreditnote,
        'totalDebitnote'        => $totalDebitnote,
        'totalForm44'           => $totalForm44,
        'totalForm45'           => $totalForm45,

        'note22Vat'             => $note22Vat,
        'note22At'              => $note22At,

        'totalIncreasing'       => $totalIncreasing,
        'totalDecreasing'       => $totalDecreasing,
        'totalPurchaseValue'    => number_format($totalPurchaseValue, 2, '.', ''),
        'totalPurchaseVat'      => number_format($totalPurchaseVat, 2, '.', ''),
        'totalAtValue'          => $totalAtValue,
        'totalDecreasingAt'     => number_format($totalDecreasingAt, 2, '.', ''),
        'totalPenalty'          => $totalPenalty,
        'closignBalance'        => $closignBalance,
        'treasury'              => $treasury,
        'note34'                => number_format($note34, 2, '.', ''),
        'prevNetPayable'        => number_format($prevNetPayable, 2, '.', ''),
        'closignBalanceCurrent' => number_format(-$closignBalanceCurrent, 2, '.', ''),
      ],
    ]);
  }

  /**
   * Calculate net payable for a client & period (YYYY-mm) using
   * the same logic as report91, with preloaded $expenseIds.
   *
   * $expenseIds = [
   *   'Increase'        => id,
   *   'Decrease'        => id,
   *   'Penalty'         => id,
   *   'Closing Balance' => id,
   * ]
   */
  protected function calculateNetPayableForPeriod(int $client_id, string $period, $expenseIds): float
  {
    $prevPeriod = date('Y-m', strtotime($period . '-01 -1 month'));

    // ---- helpers ----
    $saleSummaryByVatRate = function ($vatRate) use ($client_id, $period) {
      if (!$vatRate) return null;

      return $this->sale
        ->selectRaw("
          ROUND(COALESCE(SUM(total_price), 0), 2) AS total_price,
          ROUND(COALESCE(SUM(sd_value), 0), 2)    AS sd_value,
          ROUND(COALESCE(SUM(vat_value), 0), 2)   AS vat_value
        ")
        ->where('client_id', $client_id)
        ->where('vat', $vatRate)
        ->whereRaw("DATE_FORMAT(date, '%Y-%m') = ?", [$period])
        ->first();
    };

    $purchaseSummaryByNote = function ($mushaknoteId) use ($client_id, $period) {
      if (!$mushaknoteId) return null;

      return $this->purchase
        ->selectRaw("
          ROUND(COALESCE(SUM(base_value), 0), 2) AS base_value,
          ROUND(COALESCE(SUM(vat_value), 0), 2)  AS vat_value
        ")
        ->where('client_id', $client_id)
        ->where('mushaknote_id', $mushaknoteId)
        ->whereRaw("DATE_FORMAT(entry_date, '%Y-%m') = ?", [$period])
        ->first();
    };

    // ---- SALES (note_13, 15, 22) ----
    $notePercents = $this->mushaknote
      ->whereIn('name_note_no', ['note_13', 'note_15', 'note_22'])
      ->pluck('percent', 'name_note_no');

    $sale13 = $saleSummaryByVatRate($notePercents['note_13'] ?? null);
    $sale15 = $saleSummaryByVatRate($notePercents['note_15'] ?? null);
    $sale22 = $saleSummaryByVatRate($notePercents['note_22'] ?? null);

    $getSaleField = fn($row, $field) => $row->{$field} ?? 0;

    $totalSaleVatSum13 = $getSaleField($sale13, 'vat_value');
    $totalSaleVatSum15 = $getSaleField($sale15, 'vat_value');
    $totalSaleVatSum22 = $getSaleField($sale22, 'vat_value');

    $vatTotal = $totalSaleVatSum13 + $totalSaleVatSum15 + $totalSaleVatSum22;

    // ---- PURCHASES (note_13, 15, 22) ----
    $note13 = $this->mushaknote->where('name_note_no', 'note_13')->first();
    $note15 = $this->mushaknote->where('name_note_no', 'note_15')->first();
    $note22 = $this->mushaknote->where('name_note_no', 'note_22')->first();

    $purchase13 = $note13 ? $purchaseSummaryByNote($note13->id) : null;
    $purchase15 = $note15 ? $purchaseSummaryByNote($note15->id) : null;
    $purchase22 = $note22 ? $purchaseSummaryByNote($note22->id) : null;

    $totalPurchaseVat = (!empty($purchase15) ? $purchase15->vat_value : 0);

    // ---- INCREASE / DECREASE / PENALTY (from $expenseIds) ----
    $increasingId = $expenseIds['Increase']        ?? null;
    $decreasingId = $expenseIds['Decrease']        ?? null;
    $penaltyId    = $expenseIds['Penalty']         ?? null;
    $closingId    = $expenseIds['Closing Balance'] ?? null;

    $incAmount = $increasingId ? optional(
      $this->adjustment
        ->selectRaw("
          client_id,
          expense_id,
          tax_period,
          ROUND(COALESCE(SUM(amount), 0), 2) AS amount
        ")
        ->where('client_id', $client_id)
        ->where('tax_period', $period)
        ->where('expense_id', $increasingId)
        ->groupBy('client_id', 'expense_id', 'tax_period')
        ->first()
    )->amount ?? 0 : 0;

    $decAmount = $decreasingId ? optional(
      $this->adjustment
        ->selectRaw("
          client_id,
          expense_id,
          tax_period,
          ROUND(COALESCE(SUM(amount), 0), 2) AS amount
        ")
        ->where('client_id', $client_id)
        ->where('tax_period', $period)
        ->where('expense_id', $decreasingId)
        ->groupBy('client_id', 'expense_id', 'tax_period')
        ->first()
    )->amount ?? 0 : 0;

    $penAmount = $penaltyId ? optional(
      $this->adjustment
        ->selectRaw("
          client_id,
          expense_id,
          tax_period,
          ROUND(COALESCE(SUM(amount), 0), 2) AS amount
        ")
        ->where('client_id', $client_id)
        ->where('tax_period', $period)
        ->where('expense_id', $penaltyId)
        ->groupBy('client_id', 'expense_id', 'tax_period')
        ->first()
    )->amount ?? 0 : 0;

    // ---- AT ----
    $totalAtValueRow = $this->purchase
      ->selectRaw("
        client_id,
        ROUND(COALESCE(SUM(at_value), 0), 2) AS at_value
      ")
      ->where('client_id', $client_id)
      ->whereRaw("DATE_FORMAT(entry_date, '%Y-%m') = ?", [$period])
      ->groupBy('client_id')
      ->first();

    $atAmount = !empty($totalAtValueRow) ? $totalAtValueRow->at_value : 0;

    $decAmountWithAt = $atAmount + $decAmount;

    // ---- Closing balance (prev period, using $closingId) ----
    $closeAmount = 0;
    if ($closingId) {
      $closignBalance = $this->adjustment
        ->selectRaw("
          client_id,
          expense_id,
          tax_period,
          ROUND(COALESCE(SUM(amount), 0), 2) AS amount
        ")
        ->where('client_id', $client_id)
        ->where('expense_id', $closingId)
        ->where('tax_period', $prevPeriod)
        ->groupBy('client_id', 'expense_id', 'tax_period')
        ->first();

      $closeAmount = optional($closignBalance)->amount ?? 0;
    }

    // ---- Treasury ----
    $treasury = $this->treasury
      ->selectRaw("
        client_id,
        branch_id,
        tax_period,
        ROUND(COALESCE(SUM(amount), 0), 2) AS amount
      ")
      ->where('client_id', $client_id)
      ->where('tax_period', $period)
      ->groupBy('client_id', 'branch_id', 'tax_period')
      ->first();

    $treasuryAmount = optional($treasury)->amount ?? 0;

    // ---- Credit note ----
    $totalCreditnote = $this->creditnote
      ->selectRaw("
        client_id,
        DATE_FORMAT(issued_date, '%Y-%m') AS tax_period,
        ROUND(COALESCE(SUM(value_of_decreasing_adjustment), 0), 2) AS value
      ")
      ->where('client_id', $client_id)
      ->whereRaw("DATE_FORMAT(issued_date, '%Y-%m') = ?", [$period])
      ->groupBy('client_id', DB::raw("DATE_FORMAT(issued_date, '%Y-%m')"))
      ->first();

    $creditnoteAmount = optional($totalCreditnote)->value ?? 0;

    // ---- Debit note ----
    $totalDebitnote = $this->debitnote
      ->selectRaw("
        client_id,
        DATE_FORMAT(issued_date, '%Y-%m') AS tax_period,
        ROUND(COALESCE(SUM(value_of_increasing_adjustment), 0), 2) AS value
      ")
      ->where('client_id', $client_id)
      ->whereRaw("DATE_FORMAT(issued_date, '%Y-%m') = ?", [$period])
      ->groupBy('client_id', DB::raw("DATE_FORMAT(issued_date, '%Y-%m')"))
      ->first();

    $debitnoteAmount = optional($totalDebitnote)->value ?? 0;

    // ---- Form 4.4 ----
    $totalForm44 = $this->form44
      ->selectRaw("
        client_id,
        DATE_FORMAT(date, '%Y-%m') AS tax_period,
        ROUND(COALESCE(SUM(value), 0), 2) AS value
      ")
      ->where('client_id', $client_id)
      ->whereRaw("DATE_FORMAT(date, '%Y-%m') = ?", [$period])
      ->groupBy('client_id', DB::raw("DATE_FORMAT(date, '%Y-%m')"))
      ->first();

    $form44Amount = optional($totalForm44)->value ?? 0;

    // ---- Form 4.5 ----
    $totalForm45 = $this->form45
      ->selectRaw("
        client_id,
        DATE_FORMAT(date, '%Y-%m') AS tax_period,
        ROUND(COALESCE(SUM(value), 0), 2) AS value
      ")
      ->where('client_id', $client_id)
      ->whereRaw("DATE_FORMAT(date, '%Y-%m') = ?", [$period])
      ->groupBy('client_id', DB::raw("DATE_FORMAT(date, '%Y-%m')"))
      ->first();

    $form45Amount = optional($totalForm45)->value ?? 0;

    // ---- Final Net Payable (for this helper) ----
    $netPayable = $vatTotal - $totalPurchaseVat + $incAmount - $decAmountWithAt + $penAmount - $closeAmount - $treasuryAmount - $creditnoteAmount + $debitnoteAmount + $form44Amount + $form45Amount;

    return round(-$netPayable, 2);
  }
}
