{{-- resources/views/report44.blade.php --}}

@php
    use Carbon\Carbon;

    /** @var \App\Models\Form44 $view */
    $client = $view->client ?? null;
    $item = $view->item ?? null;

    $date = $view->date ? Carbon::parse($view->date)->format('d/m/Y') : '';

    $invoiceNo = $view->form44_invoice ?? '';
    $hsCode = $item->hs_code ?? '';
    $boe = $view->boe ?? '';
    $serial621 = $view->form621_id ?? '';
    $reason = $view->reason_ineligibility ?? '';

    $quantity = $view->quantity !== null ? number_format($view->quantity, 2, '.', '') : '';
    $value = $view->value !== null ? number_format($view->value, 2, '.', '') : '';
    $vat = $view->vat !== null ? number_format($view->vat, 2, '.', '') : '';
    $proposedValue = $view->proposed_value !== null ? number_format($view->proposed_value, 2, '.', '') : '';

    $proposedTotal =
        $view->proposed_value !== null && $view->quantity !== null
            ? number_format($view->proposed_value * $view->quantity, 2, '.', '')
            : '';
@endphp

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Report 4.4</title>

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
                    <label class="fw-bold border border-secondary border-1 position-absolute p-1 end-0" style="top: 75%">
                        Mushak-4.4
                    </label>
                    <p class="m-0">Government of the People's Republic of Bangladesh</p>
                    <p class="m-0">National Board of Revenue</p>
                </div>
            </div>
        </div>

        {{-- Title --}}
        <div class="row my-3">
            <div class="col-12 text-center">
                <p class="m-0 fw-bold">Application Form for Disposal of Unused or Unusable Materials</p>
                <p class="m-0">[See Sub-Rule (1) of Rule 24 (A)]</p>
            </div>
        </div>

        {{-- Client + Invoice block --}}
        <div class="row mb-3">
            <div class="col-12 col-sm-9 col-md-9">
                <p class="m-0">Name of Registered Person: {{ $client->company_name ?? '' }}</p>
                <p class="m-0">BIN of Registered Person: {{ $client->bin ?? '' }}</p>
                <p class="m-0">Challan Issuing Address: {{ $client->address ?? '' }}</p>
            </div>
            <div class="col-12 col-sm-3 col-md-3">
                <p class="m-0">Invoice No : {{ $invoiceNo }}</p>
                <p class="m-0">Date of Issue : {{ $date }}</p>
                <p class="m-0">HS Code : {{ $hsCode }}</p>
            </div>
        </div>

        {{-- Table --}}
        <div class="row">
            <div class="col-12">
                <div class="table-responsive">
                    <table class="table table-bordered border-secondary text-center">
                        <colgroup>
                            <col style="width: 3%" />
                        </colgroup>
                        <thead class="align-middle">
                            <tr>
                                <th>#</th>
                                <th>Item</th>
                                <th>BoE</th>
                                <th>Serial in Mushak 6.2.1</th>
                                <th>Quantity</th>
                                <th>Value</th>
                                <th>Paid VAT</th>
                                <th>Proposed Value</th>
                                <th>Reason of Ineligibility</th>
                            </tr>
                        </thead>
                        <tbody class="align-middle">
                            <tr>
                                <td>1</td>
                                <td>{{ $item->name ?? '' }}<br /> {{ $item->hs_code }}</td>
                                <td>{{ $boe }}</td>
                                <td>{{ $serial621 }}</td>
                                <td>{{ $quantity }}</td>
                                <td>{{ $value }}</td>
                                <td>{{ $vat }}</td>
                                <td>{{ $proposedTotal }}</td>
                                <td>{{ $reason }}</td>
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
                <p class="m-0">
                    * I declare that all the information provided in this application form is true and correct.
                </p>
            </div>
        </div>
    </div>

</body>

</html>
