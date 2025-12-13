{{-- resources/views/report-63-pdf.blade.php --}}

@php
    use Carbon\Carbon;

    /** @var \App\Models\YourModel $view */
    $client = $view->client ?? null;
    $item = $view->item ?? null;
    $unit = $view->unit ?? null;

    $date = $view->date ? Carbon::parse($view->date)->format('d/m/Y') : '';

    $quantity = $view->quantity !== null ? number_format($view->quantity, 2, '.', '') : '';
    $unitPrice = $view->unit_price !== null ? number_format($view->unit_price, 2, '.', '') : '';
    $itemPrice = $view->item_price !== null ? number_format($view->item_price, 2, '.', '') : '';
    $sd = $view->sd !== null ? number_format($view->sd, 2, '.', '') : '';
    $sdValue = $view->sd_value !== null ? number_format($view->sd_value, 2, '.', '') : '';
    $vat = $view->vat !== null ? number_format($view->vat, 2, '.', '') : '';
    $vatValue = $view->vat_value !== null ? number_format($view->vat_value, 2, '.', '') : '';
    $totalPrice = $view->total_price !== null ? number_format($view->total_price, 2, '.', '') : '';
@endphp

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Mushak 6.3</title>

    @php
        $css = '';

        $manifestPath = public_path('build/manifest.json');
        if (file_exists($manifestPath)) {
            $manifest = json_decode(file_get_contents($manifestPath), true);

            // ✅ this matches your Vite entry
            $entry = 'resources/css/app.css';

            if (!empty($manifest[$entry]['file'])) {
                $relativePath = 'build/' . $manifest[$entry]['file']; // e.g. build/assets/app-XXXXX.css
                $fullPath = public_path($relativePath);

                if (file_exists($fullPath)) {
                    $css = file_get_contents($fullPath);
                }
            }
        }
    @endphp

    @if ($css)
        <style>
            {!! $css !!}
        </style>
    @endif

    <style>
        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 10.5px;
            margin: 15px;
        }

        .copy-section {
            padding-bottom: 0;
            page-break-inside: avoid;
        }

        .cut-line {
            border-top: 1px dashed #000;
            margin: 20px 0;
            text-align: center;
            position: relative;
        }

        .cut-line span {
            position: absolute;
            top: -14px;
            left: 50%;
            transform: translateX(-50%);
            background: #fff;
            padding: 0 6px;
            font-size: 16px;
        }
    </style>
</head>

<body>

    {{-- ===================== --}}
    {{--      FIRST COPY      --}}
    {{-- ===================== --}}
    <div class="copy-section">
        <div class="container-fluid">
            {{-- Header --}}
            <div class="row">
                <div class="col-12">
                    <div class="fw-bold text-center position-relative">
                        <label class="fw-bold border border-secondary border-1 position-absolute p-1 end-0"
                            style="top: 75%">
                            Mushak 6.3
                        </label>
                        <p class="m-0">Government of the People's Republic of Bangladesh</p>
                        <p class="m-0">National Board of Revenue</p>
                    </div>
                </div>
            </div>

            {{-- Registered person info --}}
            <div class="row my-2">
                <div class="col-12 text-center">
                    <p class="m-0 fw-bold">Tax Challan</p>
                    <p class="m-0">[See Clauses (C) and (f) of Sub-Rule (1) of Rule 40]</p>
                    <p class="m-0">
                        Name of Registered Person:
                        {{ $client->company_name ?? '' }}
                    </p>
                    <p class="m-0">
                        BIN of Registered Person:
                        {{ $client->bin ?? '' }}
                    </p>
                    <p class="m-0">
                        Challan Issuing Address:
                        {{ $client->address ?? '' }}
                    </p>
                </div>
            </div>

            {{-- Purchaser + invoice info --}}
            <div class="row mb-2">
                <div class="col-12 col-sm-9 col-md-9">
                    <p class="m-0">
                        Name of Purchaser :
                        {{ $view->customer_name ?? '' }}
                    </p>
                    <p class="m-0">
                        BIN of Purchaser :
                        {{ $view->bin_or_nid ?? '' }}
                    </p>
                    <p class="m-0">
                        Destination of Supply :
                        {{ $view->address ?? '' }}
                    </p>
                </div>
                <div class="col-12 col-sm-3 col-md-3">
                    <p class="m-0">
                        Invoice No :
                        {{ $view->invoice_no ?? '' }}
                    </p>
                    <p class="m-0">
                        Date of Issue :
                        {{ $date }}
                    </p>
                    <p class="m-0">
                        Time of Issue :
                        {{-- leave blank or format a time field if you have one --}}
                    </p>
                </div>
            </div>

            {{-- Main table --}}
            <div class="row">
                <div class="col-12">
                    <div class="table-responsive">
                        <table class="table table-bordered border-secondary text-center">
                            <colgroup>
                                <col style="width:12%" />
                            </colgroup>
                            <thead class="align-middle">
                                <tr>
                                    <th>Goods/Service Description</th>
                                    <th>Unit of Supply</th>
                                    <th>Quantity</th>
                                    <th>Unit Value <sup>1</sup> (TAKA)</th>
                                    <th>Total Value <sup>1</sup> (TAKA)</th>
                                    <th>SD Rate (TAKA)</th>
                                    <th>SD (TAKA)</th>
                                    <th>VAT rate/Specific Tax</th>
                                    <th>VAT/Specific Tax Amount (TAKA)</th>
                                    <th>Value all kinds of Duty &amp; Tax</th>
                                </tr>
                            </thead>
                            <tbody class="align-middle">
                                <tr>
                                    <td>
                                        {{ $item->name ?? '' }}<br>
                                        ({{ $item->hs_code ?? '' }})
                                    </td>
                                    <td>{{ $unit->name ?? '' }}</td>
                                    <td>{{ $quantity }}</td>
                                    <td>{{ $unitPrice }}</td>
                                    <td>{{ $itemPrice }}</td>
                                    <td>{{ $sd }}%</td>
                                    <td>{{ $sdValue }}</td>
                                    <td>{{ $vat }}%</td>
                                    <td>{{ $vatValue }}</td>
                                    <td>{{ $totalPrice }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Footer --}}
            <div class="row my-2">
                <div class="col-12">
                    <p class="m-0">Authorised Person:</p>
                    <p class="m-0">Designation:</p>
                    <p class="m-0">Signature:</p>
                    <p class="m-0">Seal:</p>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <p class="m-0">
                        * Applicable to the supplies made to the withholding entity only and in that case it
                        will
                        be
                        used as combined tax invoice cum withholding certificate.
                    </p>
                    <p class="m-0">
                        * Value<sup>1</sup> except all kinds of Tax.
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== Scissor line between copies ===== --}}
    <div class="cut-line">
        <span>✄</span>
        {{-- <span>&#x2704;</span> --}}
    </div>

    {{-- ===================== --}}
    {{--     SECOND COPY      --}}
    {{-- ===================== --}}
    <div class="copy-section">
        <div class="container-fluid">
            {{-- Header --}}
            <div class="row">
                <div class="col-12">
                    <div class="fw-bold text-center position-relative">
                        <label class="fw-bold border border-secondary border-1 position-absolute p-1 end-0"
                            style="top: 75%">
                            Mushak 6.3
                        </label>
                        <p class="m-0">Government of the People's Republic of Bangladesh</p>
                        <p class="m-0">National Board of Revenue</p>
                    </div>
                </div>
            </div>

            {{-- Registered person info --}}
            <div class="row my-2">
                <div class="col-12 text-center">
                    <p class="m-0 fw-bold">Tax Challan</p>
                    <p class="m-0">[See Clauses (C) and (f) of Sub-Rule (1) of Rule 40]</p>
                    <p class="m-0">
                        Name of Registered Person:
                        {{ $client->company_name ?? '' }}
                    </p>
                    <p class="m-0">
                        BIN of Registered Person:
                        {{ $client->bin ?? '' }}
                    </p>
                    <p class="m-0">
                        Challan Issuing Address:
                        {{ $client->address ?? '' }}
                    </p>
                </div>
            </div>

            {{-- Purchaser + invoice info --}}
            <div class="row mb-2">
                <div class="col-12 col-sm-9 col-md-9">
                    <p class="m-0">
                        Name of Purchaser :
                        {{ $view->customer_name ?? '' }}
                    </p>
                    <p class="m-0">
                        BIN of Purchaser :
                        {{ $view->bin_or_nid ?? '' }}
                    </p>
                    <p class="m-0">
                        Destination of Supply :
                        {{ $view->address ?? '' }}
                    </p>
                </div>
                <div class="col-12 col-sm-3 col-md-3">
                    <p class="m-0">
                        Invoice No :
                        {{ $view->invoice_no ?? '' }}
                    </p>
                    <p class="m-0">
                        Date of Issue :
                        {{ $date }}
                    </p>
                    <p class="m-0">
                        Time of Issue :
                        {{-- leave blank or format a time field if you have one --}}
                    </p>
                </div>
            </div>

            {{-- Main table --}}
            <div class="row">
                <div class="col-12">
                    <div class="table-responsive">
                        <table class="table table-bordered border-secondary text-center">
                            <colgroup>
                                <col style="width:12%" />
                            </colgroup>
                            <thead class="align-middle">
                                <tr>
                                    <th>Goods/Service Description</th>
                                    <th>Unit of Supply</th>
                                    <th>Quantity</th>
                                    <th>Unit Value <sup>1</sup> (TAKA)</th>
                                    <th>Total Value <sup>1</sup> (TAKA)</th>
                                    <th>SD Rate (TAKA)</th>
                                    <th>SD (TAKA)</th>
                                    <th>VAT rate/Specific Tax</th>
                                    <th>VAT/Specific Tax Amount (TAKA)</th>
                                    <th>Value all kinds of Duty &amp; Tax</th>
                                </tr>
                            </thead>
                            <tbody class="align-middle">
                                <tr>
                                    <td>
                                        {{ $item->name ?? '' }}<br>
                                        ({{ $item->hs_code ?? '' }})
                                    </td>
                                    <td>{{ $unit->name ?? '' }}</td>
                                    <td>{{ $quantity }}</td>
                                    <td>{{ $unitPrice }}</td>
                                    <td>{{ $itemPrice }}</td>
                                    <td>{{ $sd }}%</td>
                                    <td>{{ $sdValue }}</td>
                                    <td>{{ $vat }}%</td>
                                    <td>{{ $vatValue }}</td>
                                    <td>{{ $totalPrice }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Footer --}}
            <div class="row my-2">
                <div class="col-12">
                    <p class="m-0">Authorised Person:</p>
                    <p class="m-0">Designation:</p>
                    <p class="m-0">Signature:</p>
                    <p class="m-0">Seal:</p>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <p class="m-0">
                        * Applicable to the supplies made to the withholding entity only and in that case it
                        will
                        be
                        used as combined tax invoice cum withholding certificate.
                    </p>
                    <p class="m-0">
                        * Value<sup>1</sup> except all kinds of Tax.
                    </p>
                </div>
            </div>
        </div>
    </div>

</body>

</html>
