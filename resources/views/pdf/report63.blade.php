<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Mushak 6.3</title>
    <style>
        /* DomPDF supports @page — good for margins */

        /* Reset */
        html,
        body {
            margin: 0;
            padding: 15mm;
            background: #ffffff !important;
            font-family: DejaVu Sans, sans-serif;
            /* full unicode support */
            font-size: 12px;
            color: #000000;
        }

        /* Remove shadows/borders like cards */
        .card,
        .card *,
        .panel,
        .panel * {
            border: none !important;
            box-shadow: none !important;
            background: #ffffff !important;
        }

        /* Headings */
        h1,
        h2,
        h3,
        h4,
        h5 {
            margin: 0 0 6px 0;
            font-weight: bold;
        }

        /* Utility spacing */
        .mt-0 {
            margin-top: 0 !important;
        }

        .mb-0 {
            margin-bottom: 0 !important;
        }

        .pt-0 {
            padding-top: 0 !important;
        }

        .pb-0 {
            padding-bottom: 0 !important;
        }

        /* Layout blocks */
        .container {
            width: 100%;
            margin: 0 auto;
        }

        .row {
            width: 100%;
            display: block;
            /* dompdf doesn't support flex */
        }

        .col {
            display: inline-block;
            vertical-align: top;
        }

        /* Force dompdf table behavior */
        table {
            width: 100%;
            border-collapse: collapse;
            background: #ffffff !important;
            page-break-inside: auto;
        }

        thead {
            display: table-header-group;
            /* repeat header */
        }

        tfoot {
            display: table-footer-group;
        }

        tbody {
            display: table-row-group;
        }

        tr {
            page-break-inside: avoid;
            break-inside: avoid-page;
        }

        th,
        td {
            border: 1px solid #cccccc;
            padding: 6px 8px;
            font-size: 11px;
            vertical-align: top;
        }

        th {
            background: #f3f3f3 !important;
            font-weight: bold;
        }

        /* Responsive fix: dompdf does NOT support table-responsive */
        .table-responsive {
            width: 100% !important;
            overflow: visible !important;
        }

        /* Smaller text utilities */
        .text-sm {
            font-size: 10px !important;
        }

        .text-xs {
            font-size: 9px !important;
        }

        /* Alignment */
        .text-left {
            text-align: left !important;
        }

        .text-center {
            text-align: center !important;
        }

        .text-right {
            text-align: right !important;
        }

        /* Page break classes */
        .page-break {
            page-break-after: always;
        }

        .no-page-break {
            page-break-inside: avoid !important;
        }

        /* Remove dark mode support completely */
        .theme-dark,
        [data-theme="dark"] {
            background: #ffffff !important;
            color: #000 !important;
        }

        /* Images */
        img {
            max-width: 100%;
            height: auto;
            display: block;
        }

        /* Fix large elements splitting */
        .block {
            page-break-inside: avoid;
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
