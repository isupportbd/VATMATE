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
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Style;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class Report621Export implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithDefaultStyles, WithEvents
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
      "Opening Quantity",
      "Purchase Quantity",
      "Purchase Value",
      "Total Stock",
      "Exporter Name",
      "Exporter Address",
      "BIN/NID",
      "Purchase Challan/Bill Number",
      "Purchase Challan/Bill Date",
      "Sold Item",
      "Sold Quantity",
      "Sold Taxable Value",
      "SD",
      "VAT",
      "Purchaser Name",
      "Purchaser Address",
      "Purchaser BIN/NID",
      "Sales Invoice Number",
      "Sales Invoice Date",
      "Closing Stock",
      "Remarks",
    ];
  }

  public function query()
  {
    $form621 = Form621::with(['importable', 'item', 'unit', 'client', 'form43'])
      ->where('client_id', $this->clientId)
      ->where(DB::raw("TO_CHAR(date, 'YYYY-MM')"), $this->monthYear)
      ->where('item_id', $this->itemId)
      ->orderBy('id', 'asc');

    return $form621;
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
    // 2  Opening Quantity
    // 3  Purchase Quantity
    // 4  Purchase Value
    // 5  Total Stock
    // 6  Exporter Name
    // 7  Exporter Address
    // 8  BIN/NID
    // 9 Purchase Challan/Bill Number
    // 10 Purchase Challan/Bill Date
    // 11 Sold Item
    // 12 Sold Quantity
    // 13 Sold Taxable Value
    // 14 SD
    // 15 VAT
    // 16 Purchaser Name
    // 17 Purchaser Address
    // 18 Purchaser BIN/NID
    // 19 Sales Invoice Number
    // 20 Sales Invoice Date
    // 21 Closing Stock
    // 22 Remarks (we’ll keep importable_type here)
    $row = array_fill(0, 31, '');

    // 2) common fields
    $row[0]  = $bulk->date ? date('d/m/Y', strtotime($bulk->date)) : '';
    $row[1]  = $bulk->item?->name ? $bulk->item->name . ' (' . $bulk->item?->hs_code . ')' : '';
    $row[2]  = $bulk->opening_stock . " " . $bulk->unit?->name;                                 // Opening Quantity
    $row[22] = $bulk->importable_type;                                                          // Remarks / type

    // 3) type-specific overrides

    // PURCHASE
    if ($bulk->importable_type === 'purchase') {
      $row[3]  = $bulk->quantity . " " . $bulk->unit?->name;  // Purchase Quantity
      $row[4]  = $bulk->importable?->base_value;              // Purchase Value
      $row[5]  = $bulk->stock . " " . $bulk->unit?->name;     // Total Stock
      $row[6]  = $bulk->importable?->exporter_name;           // Exporter Name
      $row[7]  = $bulk->importable?->address;                 // Exporter Address
      $row[9]  = $bulk->importable?->boe;                     // Purchase Challan/Bill Number
      $row[10] = $bulk->importable?->boe_date ? date('d/m/Y', strtotime($bulk->importable->boe_date)) : '';
      return $row;
    }

    // SALE
    if ($bulk->importable_type === 'sale') {
      $row[5]  = $bulk->opening_stock . " " . $bulk->unit?->name;     // Total Stock
      $row[11] = $bulk->item?->name ? $bulk->item->name . ' (' . $bulk->item?->hs_code . ')' : '';
      $row[12] = $bulk->quantity . " " . $bulk->unit?->name;    // Sold Quantity
      $row[13] = $bulk->importable?->total_price;               // Sold Taxable Value
      $row[14] = $bulk->importable?->sd_value;                  // SD
      $row[15] = $bulk->importable?->vat_value;                 // VAT
      $row[16] = $bulk->importable?->customer_name;             // Purchaser Name
      $row[17] = $bulk->importable?->address;                   // Purchaser Address
      $row[18] = $bulk->importable?->bin_or_nid;                // Purchaser BIN/NID
      $row[19] = $bulk->importable?->invoice_no;                // Sales Invoice Number
      $row[20] = $bulk->importable?->date ? date('d/m/Y', strtotime($bulk->importable->date)) : '';
      $row[21] = $bulk->stock . " " . $bulk->unit?->name;      // Closing Stock
      return $row;
    }

    // MUSHAK 6.7 (mushak67)
    if ($bulk->importable_type === 'mushak67') {
      $row[5]  = $bulk->stock . " " . $bulk->unit?->name;               // Total Stock
      $row[3]  = $bulk->importable?->quantity_of_decreasing_adjustment . " " . $bulk->unit?->name; // Purchase Quantity (decreasing adjustment)
      $row[4]  = $bulk->importable?->value_of_decreasing_adjustment;    // Purchase Value (decreasing)
      $row[5]  = $bulk->stock . " " . $bulk->unit?->name;               // Total Stock
      $row[9] = $bulk->importable?->creditnote_invoice;                 // Purchase Challan/Bill Number
      $row[10] = $bulk->importable?->issued_date ? date('d/m/Y', strtotime($bulk->importable?->issued_date)) : '';
      $row[21]  = $bulk->stock . " " . $bulk->unit?->name;              // Closing Stock
      return $row;
    }

    // MUSHAK 6.8 (mushak68)
    if ($bulk->importable_type === 'mushak68') {
      $row[5]  = $bulk->opening_stock . " " . $bulk->unit?->name;      // Total Stock (before adjustment)
      $row[11] = $bulk->item?->name ? $bulk->item->name . ' (' . $bulk->item?->hs_code . ')' : '';
      $row[12] = $bulk->importable?->quantity_of_increasing_adjustment . " " . $bulk->unit?->name; // Sold Quantity (increasing adj qty)
      $row[13] = $bulk->importable?->value_of_increasing_adjustment;   // Sold Taxable Value (value)
      $row[14] = $bulk->importable?->sd_of_increasing_adjustment;      // SD
      $row[15] = $bulk->importable?->vat_of_increasing_adjustment;     // VAT
      $row[19] = $bulk->importable?->debitnote_invoice;                // Sales Invoice Number (debit note)
      $row[20] = $bulk->issued_date ? date('d/m/Y', strtotime($bulk->issued_date)) : '';
      $row[21]  = $bulk->stock . " " . $bulk->unit?->name;      // Closing Stock
      return $row;
    }

    // MUSHAK 4.4 (mushak44)
    if ($bulk->importable_type === 'mushak44') {
      $row[5]  = $bulk->opening_stock . " " > $bulk->unit?->name;                               // Total Stock
      $row[11] = $bulk->item?->name ? $bulk->item->name . ' (' . $bulk->item?->hs_code . ')' : '';
      $row[12] = $bulk->importable?->quantity . " " > $bulk->unit?->name;                       // Sold Quantity
      $row[13] = $bulk->importable?->value;                                                     // Sold Taxable Value
      $row[14] = $bulk->importable?->sd;                                                        // SD
      $row[15] = $bulk->importable?->vat;                                                       // VAT
      $row[19] = $bulk->importable?->form44_invoice;                                            // Sales Invoice Number
      $row[20] = $bulk->importable?->date ? date('d/m/Y', strtotime($bulk->importable->date)) : '';
      $row[21] = $bulk->stock . " " > $bulk->unit?->name;                                       // Closing Stock
      return $row;
    }

    // MUSHAK 4.5 (mushak45)
    if ($bulk->importable_type === 'mushak45') {
      $row[5]  = $bulk->opening_stock . " " . $bulk->unit?->name;                               // Total Stock
      $row[11] = $bulk->item?->name ? $bulk->item->name . ' (' . $bulk->item?->hs_code . ')' : '';
      $row[12] = $bulk->importable?->quantity . " " . $bulk->unit?->name;                       // Sold Quantity
      $row[13] = $bulk->importable?->value;                                                     // Sold Taxable Value
      $row[14] = $bulk->importable?->sd;                                                        // SD
      $row[15] = $bulk->importable?->vat;                                                       // VAT
      $row[19] = $bulk->importable?->form45_invoice;                                            // Sales Invoice Number
      $row[20] = $bulk->importable?->date ? date('d/m/Y', strtotime($bulk->importable->date)) : '';
      $row[21]  = $bulk->stock . " " . $bulk->unit?->name;                                      // Closing Stock
      return $row;
    }

    // 4) Final fallback: always return a valid row
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
    $sheet->setCellValue('A1', 'REPORT 6.2.1');
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
