{{-- resources/views/debitnote-68.blade.php --}}

@php
    use Carbon\Carbon;

    /** @var \App\Models\DebitNote $view */

    $client = $view->client ?? null;
    $item = $view->item ?? null;

    $issuedDate = $view->issued_date ? Carbon::parse($view->issued_date)->format('d/m/Y') : '';

    // If vat_challan_date is stored as date, format; otherwise just cast to string
    $vatChallanDate = $view->vat_challan_date
        ? Carbon::parse($view->vat_challan_date)->format('d-m-Y')
        : $view->vat_challan_date ?? '';

    $debitNoteNo = $view->debitnote_invoice ?? '';
    $exporterName = $view->exporter_name ?? '';
    $reasonForIssuance = $view->reason_for_issuance ?? '';

    // Delivery info (in challan)
    $valueInChallan = number_format($view->value_in_challan, 2, '.', '') ?? '';
    $qtyInChallan = number_format($view->quantity_in_challan, 2, '.', '') ?? '';
    $vatInChallan = number_format($view->vat_in_challan, 2, '.', '') ?? '';
    $sdInChallan = number_format($view->sd_in_challan, 2, '.', '') ?? '';

    // Increasing adjustment
    $valueIncAdj = number_format($view->value_of_increasing_adjustment, 2, '.', '') ?? '';
    $qtyIncAdj = number_format($view->quantity_of_increasing_adjustment, 2, '.', '') ?? '';
    $vatIncAdj = number_format($view->vat_of_increasing_adjustment, 2, '.', '') ?? '';
    $sdIncAdj = number_format($view->sd_of_increasing_adjustment, 2, '.', '') ?? '';
@endphp

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Debitnote 6.8</title>

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
            font-size: 11px;
            margin: 15px;
        }
    </style>
</head>

<body>
    <div class="container-fluid">
        {{-- Header --}}
        <div class="row">
            <div class="col-12">
                <div class="fw-bold text-center position-relative">
                    <label class="fw-bold border border-secondary border-1 position-absolute p-1 end-0" style="top:75%">
                        Mushak-6.8
                    </label>
                    <p class="m-0">Government of the People's Republic of Bangladesh</p>
                    <p class="m-0">National Board of Revenue</p>
                </div>
            </div>
        </div>

        {{-- Title --}}
        <div class="row my-3">
            <div class="col-12 text-center">
                <p class="m-0 fw-bold">Debit Note</p>
                <p class="m-0">[See Clauses (c) and (f) of Sub-Rule (1) of Rule 40]</p>
            </div>
        </div>

        {{-- Parties info --}}
        <div class="row mb-3">
            <div class="col-12 col-sm-9 col-md-9">
                <p class="m-0">Name of Registered Person : {{ $client->company_name ?? '' }}</p>
                <p class="m-0">BIN of Registered Person : {{ $client->bin ?? '' }}</p>
                <p class="m-0">Address of Registered Person: {{ $client->address ?? '' }}</p>
                <p class="m-0">Name of Receiver : {{ $exporterName }}</p>
                <p class="m-0">BIN of Receiver : </p>
                <p class="m-0">Address of Receiver : </p>
                <p class="m-0">Vehicle Type &amp; Number : </p>
            </div>
            <div class="col-12 col-sm-3 col-md-3">
                <p class="m-0">Debit Note : {{ $debitNoteNo }}</p>
                <p class="m-0">Date of Issue : {{ $issuedDate }}</p>
                <p class="m-0">Time of Issue : </p>
            </div>
        </div>

        {{-- Table --}}
        <div class="row">
            <div class="col-12">
                <div class="table-responsive">
                    <table class="table table-bordered border-secondary text-center">
                        <col style="width:3%" />
                        <col style="width:14%" />
                        <col style="width:14%" />
                        <col />
                        <col />
                        <col />
                        <col />
                        <col style="width:8%" />
                        <col style="width:8%" />
                        <col style="width:8%" />
                        <col style="width:8%" />
                        <thead class="align-middle">
                            <tr>
                                <th rowspan="2">#</th>
                                <th rowspan="2">BoE Number &amp; Date</th>
                                <th rowspan="2">Reason for Issuance</th>
                                <th colspan="4">Delivery information mentioned in the invoice</th>
                                <th colspan="4">Related to increasing adjustment</th>
                            </tr>
                            <tr>
                                <th>Value</th>
                                <th>Quantity</th>
                                <th>VAT</th>
                                <th>SD</th>
                                <th>Value</th>
                                <th>Quantity</th>
                                <th>VAT</th>
                                <th>SD</th>
                            </tr>
                        </thead>
                        <tbody class="align-middle">
                            <tr>
                                <td>1</td>
                                <td>
                                    <p class="m-0">{{ $view->boe ?? '' }}</p>
                                    <p class="m-0">{{ $vatChallanDate }}</p>
                                </td>
                                <td>
                                    ({{ $item->name ?? '' }}) <br>
                                    {{ $reasonForIssuance }}
                                </td>
                                {{-- Delivery info in challan --}}
                                <td>{{ $valueInChallan }}</td>
                                <td>{{ $qtyInChallan }}</td>
                                <td>{{ $vatInChallan }}</td>
                                <td>{{ $sdInChallan }}</td>
                                {{-- Increasing adjustment --}}
                                <td>{{ $valueIncAdj }}</td>
                                <td>{{ $qtyIncAdj }}</td>
                                <td>{{ $vatIncAdj }}</td>
                                <td>{{ $sdIncAdj }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Footer --}}
        <div class="row my-3">
            <div class="col-12">
                <p class="m-0">Authorised Person:</p>
                <p class="m-0">Designation:</p>
                <p class="m-0">Signature:</p>
                <p class="m-0">Seal:</p>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <p class="m-0">* Value except all kinds of Tax.</p>
            </div>
        </div>
    </div>

</body>

</html>
