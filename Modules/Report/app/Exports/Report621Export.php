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
  public $form621;

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
      'Date',
      'Item',
      'Opening Quantity',
      'Unit',
      'Purchase Quantity',
      'Unit',
      'Purchase Rate',
      'Purchase Value(Excluding All Type of Taxes)',
      'Total Stock',
      'Unit',
      'Exporter Name',
      'Exporter Address',
      'Purchase Challan/Bill Number',
      'Purchase Challan/Bill Date',
      'Sold Item',
      'Sold Quantity',
      'Unit',
      'Sold Taxable Value (Excluding All Type of Taxes)',
      'Sold Rate',
      'SD Rate(%)',
      'SD',
      'VAT Rate(%)',
      'VAT',
      'Purchaser Name',
      'Purchaser Address',
      'Purchaser BIN/NID',
      'Sales Invoice Number',
      'Sales Invoice Date',
      'Closing Stock',
      'Unit',
      'Remarks'
    ];
  }

  public function query()
  {
    $form621 = Form621::with(['importable', 'item', 'unit', 'client', 'form43'])
      ->where('client_id', $this->clientId)
      ->when($this->itemId, fn($q) => $q->where('item_id', $this->itemId))
      ->when($this->monthYear, fn($q) => $q->where(DB::raw("DATE_FORMAT(date, '%Y-%m')"), $this->monthYear))
      ->orderBy('id', 'desc');

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
    // 3  Unit
    // 4  Purchase Quantity
    // 5  Unit
    // 6  Purchase Rate
    // 7  Purchase Value
    // 8  Total Stock
    // 9  Unit
    // 10 Exporter Name
    // 11 Exporter Address
    // 12 Purchase Challan/Bill Number
    // 13 Purchase Challan/Bill Date
    // 14 Sold Item
    // 15 Sold Quantity
    // 16 Unit
    // 17 Sold Taxable Value
    // 18 Sold Rate
    // 19 SD Rate(%)
    // 20 SD
    // 21 VAT Rate(%)
    // 22 VAT
    // 23 Purchaser Name
    // 24 Purchaser Address
    // 25 Purchaser BIN/NID
    // 26 Sales Invoice Number
    // 27 Sales Invoice Date
    // 28 Closing Stock
    // 29 Unit
    // 30 Remarks (we’ll keep importable_type here)
    $row = array_fill(0, 31, '');

    // 2) common fields
    $row[0]  = $bulk->date ? date('d/m/Y', strtotime($bulk->date)) : '';
    $row[1]  = $bulk->item?->name ? $bulk->item->name . '(' . $bulk->item?->hs_code . ')' : '';
    $row[2]  = $bulk->opening_stock;                                     // Opening Quantity
    $row[3]  = $bulk->unit?->name;                                       // Unit
    $row[28] = $bulk->stock;                                             // Closing Stock
    $row[29] = $bulk->unit?->name;                                       // Unit
    $row[30] = $bulk->importable_type;                                   // Remarks / type

    // 3) type-specific overrides

    // PURCHASE
    if ($bulk->importable_type === 'purchase') {
      $row[4]  = $bulk->quantity;                         // Purchase Quantity
      $row[5]  = $bulk->unit?->name;                      // Unit
      $row[6]  = $bulk->importable?->unit_price;          // Purchase Rate
      $row[7]  = $bulk->importable?->base_value;          // Purchase Value
      $row[8]  = $bulk->stock;                            // Total Stock
      $row[9]  = $bulk->unit?->name;                      // Unit
      $row[10] = $bulk->importable?->exporter_name;       // Exporter Name
      $row[11] = $bulk->importable?->address;             // Exporter Address
      $row[12] = $bulk->importable?->boe;                 // Purchase Challan/Bill Number
      $row[13] = $bulk->importable?->boe_date ? date('d/m/Y', strtotime($bulk->importable->boe_date)) : '';
      return $row;
    }

    // SALE
    if ($bulk->importable_type === 'sale') {
      $row[8]  = $bulk->opening_stock;                    // Total Stock (before sale)
      $row[9]  = $bulk->unit?->name;                      // Unit
      $row[14] = $bulk->item?->name ? $bulk->item->name . '(' . $bulk->item?->hs_code . ')' : '';
      $row[15] = $bulk->quantity;                         // Sold Quantity
      $row[16] = $bulk->unit?->name;                      // Unit
      $row[17] = $bulk->importable?->total_price;         // Sold Taxable Value
      $row[18] = $bulk->importable?->unit_price;          // Sold Rate
      $row[19] = $bulk->importable?->sd;                  // SD Rate(%)
      $row[20] = $bulk->importable?->sd_value;            // SD
      $row[21] = $bulk->importable?->vat;                 // VAT Rate(%)
      $row[22] = $bulk->importable?->vat_value;           // VAT
      $row[23] = $bulk->importable?->customer_name;       // Purchaser Name
      $row[24] = $bulk->importable?->address;             // Purchaser Address
      $row[25] = $bulk->importable?->bin_or_nid;          // Purchaser BIN/NID
      $row[26] = $bulk->importable?->invoice_no;          // Sales Invoice Number
      $row[27] = $bulk->importable?->date ? date('d/m/Y', strtotime($bulk->importable->date)) : '';
      return $row;
    }

    // MUSHAK 6.7 (mushak67)
    if ($bulk->importable_type === 'mushak67') {
      $row[4]  = $bulk->importable?->quantity_of_decreasing_adjustment; // Purchase Quantity (decreasing adjustment)
      $row[5]  = $bulk->unit?->name;                                    // Unit
      $row[6]  = $bulk->importable?->rate_in_challan;                  // Purchase Rate (credit note)
      $row[7]  = $bulk->importable?->value_of_decreasing_adjustment;   // Purchase Value (decreasing)
      $row[8]  = $bulk->opening_stock;                                 // Total Stock
      $row[9]  = $bulk->unit?->name;                                   // Unit
      $row[12] = $bulk->importable?->creditnote_invoice;               // Purchase Challan/Bill Number
      $row[13] = $bulk->issued_date ? date('d/m/Y', strtotime($bulk->issued_date)) : '';
      return $row;
    }

    // MUSHAK 6.8 (mushak68)
    if ($bulk->importable_type === 'mushak68') {
      $row[8]  = $bulk->opening_stock;                                 // Total Stock (before adjustment)
      $row[9]  = $bulk->unit?->name;                                   // Unit
      $row[14] = $bulk->item?->name ? $bulk->item->name . '(' . $bulk->item?->hs_code . ')' : '';
      $row[15] = $bulk->importable?->quantity_of_increasing_adjustment; // Sold Quantity (increasing adj qty)
      $row[16] = $bulk->unit?->name;                                   // Unit
      $row[17] = $bulk->importable?->value_of_increasing_adjustment;   // Sold Taxable Value (value)
      $row[18] = $bulk->importable?->rate_in_challan;                  // Sold Rate
      // SD Rate, SD, VAT Rate, VAT in adjustment
      $row[20] = $bulk->importable?->sd_of_increasing_adjustment;      // SD
      $row[22] = $bulk->importable?->vat_of_increasing_adjustment;     // VAT
      $row[26] = $bulk->importable?->debitnote_invoice;                // Sales Invoice Number (debit note)
      $row[27] = $bulk->issued_date ? date('d/m/Y', strtotime($bulk->issued_date)) : '';
      return $row;
    }

    // MUSHAK 4.4 (mushak44)
    if ($bulk->importable_type === 'mushak44') {
      $row[8]  = $bulk->opening_stock;                               // Total Stock
      $row[9]  = $bulk->unit?->name;                                 // Unit
      $row[14] = $bulk->item?->name ? $bulk->item->name . '(' . $bulk->item?->hs_code . ')' : '';
      $row[15] = $bulk->importable?->quantity;                       // Sold Quantity
      $row[16] = $bulk->unit?->name;                                 // Unit
      $row[17] = $bulk->importable?->value;                          // Sold Taxable Value
      $row[22] = $bulk->importable?->vat;                            // VAT
      $row[26] = $bulk->importable?->form44_invoice;                 // Sales Invoice Number
      $row[27] = $bulk->importable?->date ? date('d/m/Y', strtotime($bulk->importable->date)) : '';
      return $row;
    }

    // MUSHAK 4.5 (mushak45)
    if ($bulk->importable_type === 'mushak45') {
      $row[8]  = $bulk->opening_stock;                               // Total Stock
      $row[9]  = $bulk->unit?->name;                                 // Unit
      $row[14] = $bulk->item?->name ? $bulk->item->name . '(' . $bulk->item?->hs_code . ')' : '';
      $row[15] = $bulk->importable?->quantity;                       // Sold Quantity
      $row[16] = $bulk->unit?->name;                                 // Unit
      $row[17] = $bulk->importable?->value;                          // Sold Taxable Value
      $row[22] = $bulk->importable?->vat;                            // VAT
      $row[26] = $bulk->importable?->form45_invoice;                 // Sales Invoice Number
      $row[27] = $bulk->importable?->date ? date('d/m/Y', strtotime($bulk->importable->date)) : '';
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

    $sheet->getStyle("A4:A11")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setVertical(Alignment::VERTICAL_CENTER);
    $sheet->getStyle("C4:C11")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setVertical(Alignment::VERTICAL_CENTER);

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
        'horizontal' => Alignment::HORIZONTAL_CENTER,
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
