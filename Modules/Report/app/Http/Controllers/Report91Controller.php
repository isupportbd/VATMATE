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

class Report91Controller extends Controller
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
    $prevPeriod = date('Y-m', strtotime($period . '-01 -1 month'));

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
          ROUND(COALESCE(SUM(item_price), 0), 2) AS item_price,
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
    $totalSaleValueSum13  = $getSaleField($sale13, 'item_price');
    $totalSaleValueSum15  = $getSaleField($sale15, 'item_price');
    $totalSaleValueSum22  = $getSaleField($sale22, 'item_price');

    $totalSaleSdSum13  = $getSaleField($sale13, 'sd_value');
    $totalSaleSdSum15  = $getSaleField($sale15, 'sd_value');
    $totalSaleSdSum22  = $getSaleField($sale22, 'sd_value');

    $totalSaleVatSum13  = $getSaleField($sale13, 'vat_value');
    $totalSaleVatSum15  = $getSaleField($sale15, 'vat_value');
    $totalSaleVatSum22  = $getSaleField($sale22, 'vat_value');

    $totalSaleValue = $totalSaleValueSum13 + $totalSaleValueSum15 + $totalSaleValueSum22;
    $totalSaleSd    = $totalSaleSdSum13 + $totalSaleSdSum15 + $totalSaleSdSum22;
    $totalSaleVat   = $totalSaleVatSum13 + $totalSaleVatSum15 + $totalSaleVatSum22;

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

    $totalPurchaseValue = (optional($purchase13)->base_value + optional($purchase15)->base_value + optional($purchase22)->base_value) ?? 0;
    $totalPurchaseVat = optional($purchase15)->vat ?? 0;

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

    $totalAtValue = optional($totalAtValue)->at_value ?? 0;

    $totalDecreasingAt = (!empty($totalAtValue) ? $totalAtValue : 0) + (!empty($totalDecreasing) ? $totalDecreasing->amount : 0);

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
      ROUND(COALESCE(SUM(vat_of_decreasing_adjustment), 0), 2) AS decrease_vat,
      ROUND(COALESCE(SUM(sd_of_decreasing_adjustment), 0), 2) AS decrease_sd
    ")
      ->where('client_id', $client_id)
      ->whereRaw('DATE_FORMAT(issued_date, "%Y-%m") = ?', [$period])
      ->groupBy('client_id', DB::raw("DATE_FORMAT(issued_date, '%Y-%m')"))
      ->first();

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

    // ----- EXPENSE IDs -----
    $expenseIds = $this->expense->whereIn('name', ['Closing Balance', 'Increase', 'Decrease', 'Penalty'])->pluck('id', 'name');

    // ----- PREVIOUS CLOSING BALANCE -----
    $closingId      = $expenseIds['Closing Balance'] ?? null;
    $prevNetPayable = 0;
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
    }

    // ----- NOTE CALCULATION -----
    // | Net Payable VAT:
    // | Total Sales VAT (local+import)
    // | – (Total Purchase VAT)
    // | + Total Increasing Adjustment
    // | – Total Decreasing Adjustment + Penalty
    // | – Total AT – Closing Balance of Last Tax Period – Treasury

    $note3    = $totalSaleValueSum13 ?? 0;
    $note4a   = $totalSaleValueSum15 ?? 0;
    $note4b   = $totalSaleSdSum15 ?? 0;
    $note4c   = $totalSaleVatSum15 ?? 0;
    $note8a   = $totalSaleValueSum22 ?? 0;
    $note8b   = $totalSaleSdSum22 ?? 0;
    $note8c   = $totalSaleVatSum22 ?? 0;
    $note9a   = $totalSaleValue ?? 0;
    $note9b   = $totalSaleSd ?? 0;
    $note9c   = $totalSaleVat ?? 0;
    $note13a  = optional($purchase13)->base_value ?? 0;
    $note13b  = optional($purchase13)->vat_value ?? 0;
    $note15a  = optional($purchase15)->base_value ?? 0;
    $note15b  = optional($purchase15)->vat_value ?? 0;
    $note22a  = optional($purchase22)->base_value ?? 0;
    $note22b  = optional($purchase22)->vat_value ?? 0;
    $note23a  = $totalPurchaseValue ?? 0;
    $note23b  = $totalPurchaseVat ?? 0;
    $note26   = optional($totalDebitnote)->increase_vat ?? 0;
    $note27   = optional($totalIncreasing)->amount + optional($totalForm44)->increase_vat + optional($totalForm45)->increase_vat ?? 0;
    $note28   = ($note26 + $note27) ?? 0;
    $note30   = $totalAtValue ?? 0;
    $note31   = optional($totalCreditnote)->decrease_vat ?? 0;
    $note32   = optional($totalDecreasing)->amount ?? 0;
    $note33   = ($note30 + $note31 + $note32) ?? 0;
    $note34   = ($note9c - $note23b + $note28 - $note33) ?? 0;
    $note35   = ($note34 - $prevNetPayable) ?? 0;
    $note38   = optional($totalDebitnote)->increase_sd ?? 0;
    $note39   = optional($totalCreditnote)->decrease_sd ?? 0;
    $note44   = optional($totalPenalty)->amount ?? 0;
    $note50   = ($note35 + $note44) ?? 0;
    $note52   = $prevNetPayable ?? 0;
    $note58   = optional($treasury)->amount ?? 0;
    $note65   = ($note58 - $note50) ?? 0;

    if ($closingId) {
      $this->adjustment->updateOrCreate(
        [
          'client_id'  => $client_id,
          'expense_id' => $closingId,
          'tax_period' => $period, // current month
        ],
        [
          'amount'     => $note65,
        ]
      );
    }

    /*
    |--------------------------------------------------------------------------
    | RESPONSE
    |--------------------------------------------------------------------------
    */
    return Inertia::render('report/report91/index', [
      'report91' => [
        'client'  => $client,
        'note3'   => number_format($note3, 2, ".", ""),
        'note4a'  => number_format($note4a, 2, ".", ""),
        'note4b'  => number_format($note4b, 2, ".", ""),
        'note4c'  => number_format($note4c, 2, ".", ""),
        'note8a'  => number_format($note8a, 2, ".", ""),
        'note8b'  => number_format($note8b, 2, ".", ""),
        'note8c'  => number_format($note8c, 2, ".", ""),
        'note9a'  => number_format($note9a, 2, ".", ""),
        'note9b'  => number_format($note9b, 2, ".", ""),
        'note9c'  => number_format($note9c, 2, ".", ""),
        'note13a' => number_format($note13a, 2, ".", ""),
        'note13b' => number_format($note13b, 2, ".", ""),
        'note15a' => number_format($note15a, 2, ".", ""),
        'note15b' => number_format($note15b, 2, ".", ""),
        'note22a' => number_format($note22a, 2, ".", ""),
        'note22b' => number_format($note22b, 2, ".", ""),
        'note23a' => number_format($note23a, 2, ".", ""),
        'note23b' => number_format($note23b, 2, ".", ""),
        'note26'  => number_format($note26, 2, ".", ""),
        'note27'  => number_format($note27, 2, ".", ""),
        'note28'  => number_format($note28, 2, ".", ""),
        'note30'  => number_format($note30, 2, ".", ""),
        'note31'  => number_format($note31, 2, ".", ""),
        'note32'  => number_format($note32, 2, ".", ""),
        'note33'  => number_format($note33, 2, ".", ""),
        'note34'  => number_format($note34, 2, ".", ""),
        'note35'  => number_format($note35, 2, ".", ""),
        'note38'  => number_format($note38, 2, ".", ""),
        'note39'  => number_format($note39, 2, ".", ""),
        'note44'  => number_format($note44, 2, ".", ""),
        'note50'  => number_format($note50, 2, ".", ""),
        'note52'  => number_format($note52, 2, ".", ""),
        'note58'  => number_format($note58, 2, ".", ""),
        'note65'  => number_format($note65, 2, ".", ""),
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
  // protected function calculateNetPayableForPeriod(int $client_id, string $period, $expenseIds): float
  // {
  //   $prevPeriod = date('Y-m', strtotime($period . '-01 -1 month'));

  //   // ---- helpers ----
  //   $saleSummaryByVatRate = function ($vatRate) use ($client_id, $period) {
  //     if (!$vatRate) return null;

  //     return $this->sale
  //       ->selectRaw("
  //         ROUND(COALESCE(SUM(total_price), 0), 2) AS total_price,
  //         ROUND(COALESCE(SUM(sd_value), 0), 2)    AS sd_value,
  //         ROUND(COALESCE(SUM(vat_value), 0), 2)   AS vat_value
  //       ")
  //       ->where('client_id', $client_id)
  //       ->where('vat', $vatRate)
  //       ->whereRaw("DATE_FORMAT(date, '%Y-%m') = ?", [$period])
  //       ->first();
  //   };

  //   $purchaseSummaryByNote = function ($mushaknoteId) use ($client_id, $period) {
  //     if (!$mushaknoteId) return null;

  //     return $this->purchase
  //       ->selectRaw("
  //         ROUND(COALESCE(SUM(base_value), 0), 2) AS base_value,
  //         ROUND(COALESCE(SUM(vat_value), 0), 2)  AS vat_value
  //       ")
  //       ->where('client_id', $client_id)
  //       ->where('mushaknote_id', $mushaknoteId)
  //       ->whereRaw("DATE_FORMAT(entry_date, '%Y-%m') = ?", [$period])
  //       ->first();
  //   };

  //   // ---- SALES (note_13, 15, 22) ----
  //   $notePercents = $this->mushaknote
  //     ->whereIn('name_note_no', ['note_13', 'note_15', 'note_22'])
  //     ->pluck('percent', 'name_note_no');

  //   $sale13 = $saleSummaryByVatRate($notePercents['note_13'] ?? null);
  //   $sale15 = $saleSummaryByVatRate($notePercents['note_15'] ?? null);
  //   $sale22 = $saleSummaryByVatRate($notePercents['note_22'] ?? null);

  //   $getSaleField = fn($row, $field) => $row->{$field} ?? 0;

  //   $totalSaleVatSum13 = $getSaleField($sale13, 'vat_value');
  //   $totalSaleVatSum15 = $getSaleField($sale15, 'vat_value');
  //   $totalSaleVatSum22 = $getSaleField($sale22, 'vat_value');

  //   $vatTotal = $totalSaleVatSum13 + $totalSaleVatSum15 + $totalSaleVatSum22;

  //   // ---- PURCHASES (note_13, 15, 22) ----
  //   $note13 = $this->mushaknote->where('name_note_no', 'note_13')->first();
  //   $note15 = $this->mushaknote->where('name_note_no', 'note_15')->first();
  //   $note22 = $this->mushaknote->where('name_note_no', 'note_22')->first();

  //   $purchase13 = $note13 ? $purchaseSummaryByNote($note13->id) : null;
  //   $purchase15 = $note15 ? $purchaseSummaryByNote($note15->id) : null;
  //   $purchase22 = $note22 ? $purchaseSummaryByNote($note22->id) : null;

  //   $totalPurchaseVat = (!empty($purchase15) ? $purchase15->vat_value : 0);

  //   // ---- INCREASE / DECREASE / PENALTY (from $expenseIds) ----
  //   $increasingId = $expenseIds['Increase']        ?? null;
  //   $decreasingId = $expenseIds['Decrease']        ?? null;
  //   $penaltyId    = $expenseIds['Penalty']         ?? null;
  //   $closingId    = $expenseIds['Closing Balance'] ?? null;

  //   $incAmount = $increasingId ? optional(
  //     $this->adjustment
  //       ->selectRaw("
  //         client_id,
  //         expense_id,
  //         tax_period,
  //         ROUND(COALESCE(SUM(amount), 0), 2) AS amount
  //       ")
  //       ->where('client_id', $client_id)
  //       ->where('tax_period', $period)
  //       ->where('expense_id', $increasingId)
  //       ->groupBy('client_id', 'expense_id', 'tax_period')
  //       ->first()
  //   )->amount ?? 0 : 0;

  //   $decAmount = $decreasingId ? optional(
  //     $this->adjustment
  //       ->selectRaw("
  //         client_id,
  //         expense_id,
  //         tax_period,
  //         ROUND(COALESCE(SUM(amount), 0), 2) AS amount
  //       ")
  //       ->where('client_id', $client_id)
  //       ->where('tax_period', $period)
  //       ->where('expense_id', $decreasingId)
  //       ->groupBy('client_id', 'expense_id', 'tax_period')
  //       ->first()
  //   )->amount ?? 0 : 0;

  //   $penAmount = $penaltyId ? optional(
  //     $this->adjustment
  //       ->selectRaw("
  //         client_id,
  //         expense_id,
  //         tax_period,
  //         ROUND(COALESCE(SUM(amount), 0), 2) AS amount
  //       ")
  //       ->where('client_id', $client_id)
  //       ->where('tax_period', $period)
  //       ->where('expense_id', $penaltyId)
  //       ->groupBy('client_id', 'expense_id', 'tax_period')
  //       ->first()
  //   )->amount ?? 0 : 0;

  //   // ---- AT ----
  //   $totalAtValueRow = $this->purchase
  //     ->selectRaw("
  //       client_id,
  //       ROUND(COALESCE(SUM(at_value), 0), 2) AS at_value
  //     ")
  //     ->where('client_id', $client_id)
  //     ->whereRaw("DATE_FORMAT(entry_date, '%Y-%m') = ?", [$period])
  //     ->groupBy('client_id')
  //     ->first();

  //   $atAmount = !empty($totalAtValueRow) ? $totalAtValueRow->at_value : 0;

  //   $decAmountWithAt = $atAmount + $decAmount;

  //   // ---- Closing balance (prev period, using $closingId) ----
  //   $closeAmount = 0;
  //   if ($closingId) {
  //     $closignBalance = $this->adjustment
  //       ->selectRaw("
  //         client_id,
  //         expense_id,
  //         tax_period,
  //         ROUND(COALESCE(SUM(amount), 0), 2) AS amount
  //       ")
  //       ->where('client_id', $client_id)
  //       ->where('expense_id', $closingId)
  //       ->where('tax_period', $prevPeriod)
  //       ->groupBy('client_id', 'expense_id', 'tax_period')
  //       ->first();

  //     $closeAmount = optional($closignBalance)->amount ?? 0;
  //   }

  //   // ---- Treasury ----
  //   $treasury = $this->treasury
  //     ->selectRaw("
  //       client_id,
  //       branch_id,
  //       tax_period,
  //       ROUND(COALESCE(SUM(amount), 0), 2) AS amount
  //     ")
  //     ->where('client_id', $client_id)
  //     ->where('tax_period', $period)
  //     ->groupBy('client_id', 'branch_id', 'tax_period')
  //     ->first();

  //   $treasuryAmount = optional($treasury)->amount ?? 0;

  //   // ---- Credit note ----
  //   $totalCreditnote = $this->creditnote
  //     ->selectRaw("
  //       client_id,
  //       DATE_FORMAT(issued_date, '%Y-%m') AS tax_period,
  //       ROUND(COALESCE(SUM(value_of_decreasing_adjustment), 0), 2) AS value
  //     ")
  //     ->where('client_id', $client_id)
  //     ->whereRaw("DATE_FORMAT(issued_date, '%Y-%m') = ?", [$period])
  //     ->groupBy('client_id', DB::raw("DATE_FORMAT(issued_date, '%Y-%m')"))
  //     ->first();

  //   $creditnoteAmount = optional($totalCreditnote)->value ?? 0;

  //   // ---- Debit note ----
  //   $totalDebitnote = $this->debitnote
  //     ->selectRaw("
  //       client_id,
  //       DATE_FORMAT(issued_date, '%Y-%m') AS tax_period,
  //       ROUND(COALESCE(SUM(value_of_increasing_adjustment), 0), 2) AS value
  //     ")
  //     ->where('client_id', $client_id)
  //     ->whereRaw("DATE_FORMAT(issued_date, '%Y-%m') = ?", [$period])
  //     ->groupBy('client_id', DB::raw("DATE_FORMAT(issued_date, '%Y-%m')"))
  //     ->first();

  //   $debitnoteAmount = optional($totalDebitnote)->value ?? 0;

  //   // ---- Form 4.4 ----
  //   $totalForm44 = $this->form44
  //     ->selectRaw("
  //       client_id,
  //       DATE_FORMAT(date, '%Y-%m') AS tax_period,
  //       ROUND(COALESCE(SUM(value), 0), 2) AS value
  //     ")
  //     ->where('client_id', $client_id)
  //     ->whereRaw("DATE_FORMAT(date, '%Y-%m') = ?", [$period])
  //     ->groupBy('client_id', DB::raw("DATE_FORMAT(date, '%Y-%m')"))
  //     ->first();

  //   $form44Amount = optional($totalForm44)->value ?? 0;

  //   // ---- Form 4.5 ----
  //   $totalForm45 = $this->form45
  //     ->selectRaw("
  //       client_id,
  //       DATE_FORMAT(date, '%Y-%m') AS tax_period,
  //       ROUND(COALESCE(SUM(value), 0), 2) AS value
  //     ")
  //     ->where('client_id', $client_id)
  //     ->whereRaw("DATE_FORMAT(date, '%Y-%m') = ?", [$period])
  //     ->groupBy('client_id', DB::raw("DATE_FORMAT(date, '%Y-%m')"))
  //     ->first();

  //   $form45Amount = optional($totalForm45)->value ?? 0;

  //   // ---- Final Net Payable (for this helper) ----
  //   $netPayable = $vatTotal - $totalPurchaseVat + $incAmount - $decAmountWithAt + $penAmount - $closeAmount - $treasuryAmount - $creditnoteAmount + $debitnoteAmount + $form44Amount + $form45Amount;

  //   return round(-$netPayable, 2);
  // }
}
