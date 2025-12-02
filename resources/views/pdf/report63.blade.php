<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Mushak 6.3</title>
    <style>
        /* For browser preview only (ignored by dompdf's @page) */
        body {
            margin: 0;
            padding: 0;
            background: #e0e0e0;
            /* grey so you can see the page */
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
        }

        .page {
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            background: #ffffff;
            /* padding: 5mm; */
            box-sizing: border-box;
        }

        /* For PDF: dompdf uses @page */
        @page {
            size: A4;
            margin: 12mm;
        }

        .scale {
            transform: scale(0.90);
            transform-origin: top left;
        }

        .text-center {
            text-align: center;
        }

        .fw-bold {
            font-weight: bold;
        }

        .m-0 {
            margin: 0;
        }

        .mb-1 {
            margin-bottom: 4px;
        }

        .mb-2 {
            margin-bottom: 8px;
        }

        .mb-3 {
            margin-bottom: 12px;
        }

        .my-3 {
            margin-top: 12px;
            margin-bottom: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
        }

        th,
        td {
            border: 1px solid #444;
            padding: 3px 4px;
        }

        .no-border-table th,
        .no-border-table td {
            border: none;
            padding: 0;
        }

        .header-block {
            margin-bottom: 12px;
        }

        .info-table td {
            padding: 2px 0;
        }

        .footer-text {
            font-size: 10px;
        }
    </style>
</head>

<body>
    <div class="page scale">
        {{-- Heading --}}
        <div class="text-center header-block">
            <p class="m-0 fw-bold">Mushak 6.3</p>
            <p class="m-0">Government of the People's Republic of Bangladesh</p>
            <p class="m-0">National Board of Revenue</p>
        </div>

        {{-- Title + Registered person --}}
        <div class="text-center mb-3">
            <p class="m-0 fw-bold">Tax Challan</p>
            <p class="m-0">[See Clauses (C) and (f) of Sub-Rule (1) of Rule 40]</p>
            <p class="m-0">Name of Registered Person: {{ $view->client->company_name ?? '' }}</p>
            <p class="m-0">BIN of Registered Person: {{ $view->client->bin ?? '' }}</p>
            <p class="m-0">Challan Issuing Address: {{ $view->client->address ?? '' }}</p>
        </div>

        {{-- Purchaser + Invoice info in a 2-column table (stable in dompdf) --}}
        <table class="no-border-table info-table mb-3">
            <tr>
                <td style="width: 65%;">
                    <p class="m-0 mb-1">Name of Purchaser : {{ $view->customer_name }}</p>
                    <p class="m-0 mb-1">BIN of Purchaser : {{ $view->bin_or_nid }}</p>
                    <p class="m-0 mb-1">Destination of Supply : {{ $view->address }}</p>
                </td>
                <td style="width: 35%;">
                    <p class="m-0 mb-1">Invoice No : {{ $view->invoice_no }}</p>
                    <p class="m-0 mb-1">
                        Date of Issue :
                        {{ \Carbon\Carbon::parse($view->date)->format('d/m/Y') }}
                    </p>
                    <p class="m-0 mb-1">Time of Issue :</p>
                </td>
            </tr>
        </table>

        {{-- Main table --}}
        <table>
            <thead>
                <tr>
                    <th style="width: 20%;">Goods/Service Description (Brand)</th>
                    <th style="width: 10%;">Unit of Supply</th>
                    <th style="width: 8%;">Quantity</th>
                    <th style="width: 10%;">Unit Value<sup>1</sup> (TAKA)</th>
                    <th style="width: 12%;">Total Value<sup>1</sup> (TAKA)</th>
                    <th style="width: 7%;">SD Rate</th>
                    <th style="width: 8%;">SD (TAKA)</th>
                    <th style="width: 10%;">VAT rate / Specific Tax</th>
                    <th style="width: 10%;">VAT / Specific Tax Amount (TAKA)</th>
                    <th style="width: 10%;">Value all kinds of Duty & Tax</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        {{ $view->item->name ?? '' }}<br>
                        ({{ $view->item->hs_code ?? '' }})
                    </td>
                    <td>{{ $view->unit->name ?? '' }}</td>
                    <td>{{ number_format($view->quantity ?? 0, 2) }}</td>
                    <td>{{ number_format($view->unit_price ?? 0, 2) }}</td>
                    <td>{{ number_format($view->item_price ?? 0, 2) }}</td>
                    <td>{{ number_format($view->sd ?? 0, 2) }}%</td>
                    <td>{{ number_format($view->sd_value ?? 0, 2) }}</td>
                    <td>{{ number_format($view->vat ?? 0, 2) }}%</td>
                    <td>{{ number_format($view->vat_value ?? 0, 2) }}</td>
                    <td>{{ number_format($view->total_price ?? 0, 2) }}</td>
                </tr>
            </tbody>
        </table>

        {{-- Signature block --}}
        <div class="my-3">
            <p class="m-0">Authorised Person:</p>
            <p class="m-0">Designation:</p>
            <p class="m-0">Signature:</p>
            <p class="m-0">Seal:</p>
        </div>

        {{-- Footer notes --}}
        <div class="footer-text">
            <p class="m-0">
                * Applicable to the supplies made to the withholding entity only and in that case
                it will be used as combined tax invoice cum withholding certificate.
            </p>
            <p class="m-0">
                * Value<sup>1</sup> except all kinds of Tax.
            </p>
        </div>
    </div>
</body>

</html>
