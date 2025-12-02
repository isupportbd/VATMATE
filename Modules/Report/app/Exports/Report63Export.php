<?php

namespace Modules\Report\Exports;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithDefaultStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Events\BeforeSheet;
use Modules\Client\Models\Client;
use Modules\Form621\Models\Form621;
use Modules\Sale\Models\Sale;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Style;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class Report63Export implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithDefaultStyles, WithEvents
{
  public $clientId;
  public $itemId;
  public $monthYear;

  use Exportable;

  public function __construct($clientId, $itemId, $monthYear)
  {
    $this->clientId = $clientId;
    $this->itemId = $itemId;
    $this->monthYear = $monthYear;
  }

  public function headings(): array
  {
    return [
      "Date",
      "Item",
      "Invoice",
      "Quantity",
      "Unit Price",
      "Item Price",
      "VAT",
      "Total Price",
      "Customer Name",
      "Address",
      "Mobile",
    ];
  }

  public function query()
  {
    $sale = Sale::with(['client', 'item', 'unit', 'bank'])
      ->where('client_id', $this->clientId)
      ->when($this->itemId, fn($q) => $q->where('item_id', $this->itemId))
      ->when($this->monthYear, fn($q) => $q->where(DB::raw("DATE_FORMAT(date, '%Y-%m')"), $this->monthYear))
      ->orderBy('id', 'asc');

    return $sale;
  }

  public function chunkSize(): int
  {
    return 1000; // Process in chunks of 1000
  }

  public function batchSize(): int
  {
    return 1000; // Insert in batches of 1000
  }

  public function map($bulk): array
  {
    // 1) base row with 31 empty cells
    // Index mapping (0-based):
    // 0  Date
    // 1  Item
    // 2  Invoice
    // 3  Quantity
    // 4  Unit Price
    // 5  Item Price
    // 6  VAT
    // 7  Total Price
    // 8  Customer Name
    // 9  Address
    // 10 Mobile
    $row = array_fill(0, 10, '');

    // 2) common fields
    $row[0]  = $bulk->date ? date('d/m/Y', strtotime($bulk->date)) : '';
    $row[1]  = $bulk->item?->name ? $bulk->item->name . ' (' . $bulk->item?->hs_code . ')' : '';
    $row[2]  = $bulk->invoice_no;
    $row[3]  = $bulk->quantity . " " . $bulk->unit?->name;
    $row[4]  = $bulk->unit_price;
    $row[5]  = $bulk->item_price;
    $row[6]  = $bulk->vat_value;
    $row[7]  = $bulk->total_price;
    $row[8]  = $bulk->customer_name;
    $row[9]  = $bulk->customer_address;
    $row[10] = $bulk->customer_mobile;

    return $row;
  }

  public function defaultStyles(Style $defaultStyle)
  {
    return [
      'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_LEFT,
        'vertical' => Alignment::VERTICAL_CENTER,
        'wrapText' => true,
      ],
    ];
  }

  public function styles(Worksheet $sheet)
  {
    return [
      // Style the first row as bold text.
      1 => ['font' => ['bold' => true]],
    ];
  }

  public function registerEvents(): array
  {
    return [
      AfterSheet::class => function (AfterSheet $event) {
        $sheet = $event->sheet->getDelegate();
        $this->applyFormatting($sheet);
      }
    ];
  }

  public function setupHeaders($sheet)
  {
    // Similarly for other relations if you want names instead of IDs
    $client = isset($this->clientId) ? Client::find($this->clientId) : null;

    $meta = [
      ['A4', 'Company :', 'C4', $client?->company_name],
      ['A5', 'BIN :',     'C5', $client?->bin],
      ['A6', 'Month :',   'C6', date("M Y", strtotime($this->monthYear))],
    ];

    $sheet->getStyle("A4:A6")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setVertical(Alignment::VERTICAL_CENTER);
    $sheet->getStyle("C4:C6")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setVertical(Alignment::VERTICAL_CENTER);

    foreach ($meta as [$labelCell, $label, $valueCell, $value]) {
      $row = substr($labelCell, 1);
      $sheet->mergeCells("A{$row}:B{$row}");
      $sheet->setCellValue($labelCell, $label);
      $sheet->getStyle($labelCell)->getFont()->setBold(true);

      $sheet->mergeCells("C{$row}:D{$row}");
      $sheet->setCellValue($valueCell, $value);
      $sheet->getStyle($valueCell)->getFont()->setBold(true);
    }
  }

  public function applyFormatting(Worksheet $sheet)
  {
    $headings = $this->headings();
    $colCount = count($headings);
    $lastCol  = $this->colLetter($colCount);

    // 1) Shift everything (headings + data) down by 7 rows
    //    Row 1 -> 8, Row 2 -> 9, etc.
    $sheet->insertNewRowBefore(1, 7);

    // 2) Title across row 1–2
    $sheet->mergeCells("A1:{$lastCol}2");
    $sheet->setCellValue('A1', 'REPORT 6.3');
    $sheet->getStyle("A1:{$lastCol}2")->applyFromArray([
      'font' => ['bold' => true, 'size' => 20],
      'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_LEFT,
        'vertical'   => Alignment::VERTICAL_CENTER,
      ],
    ]);

    // 3) Company / BIN / Month in rows 4–6
    $this->setupHeaders($sheet);

    // 4) Headings row is now 8 (because original row 1 moved to row 8)
    $headingRow = 8;
    $sheet->getStyle("A{$headingRow}:{$lastCol}{$headingRow}")->applyFromArray(['font' => ['bold' => true]]);

    // Extra safeguard: title bold
    $sheet->getStyle("A1:{$lastCol}1")->applyFromArray(['font' => ['bold' => true]]);
  }

  /** Helper: 1-based index to Excel letters */
  protected function colLetter(int $index): string
  {
    // 1 -> A, 26 -> Z, 27 -> AA, ...
    $letters = '';
    while ($index > 0) {
      $index--; // shift to 0-based
      $letters = chr(65 + ($index % 26)) . $letters;
      $index = intdiv($index, 26);
    }
    return $letters;
  }
}
