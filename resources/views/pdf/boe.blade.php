{{-- resources/views/pdf/boe.blade.php --}}

@php
    use Carbon\Carbon;

    // $boe is an ARRAY passed from controller
    $client = $boe['client'] ?? null;
    $purchases = $boe['purchase'] ?? collect();
    $totalBoe = $boe['totalBoe'] ?? 0;

    // make sure purchases is a collection
    if (!$purchases instanceof \Illuminate\Support\Collection) {
        $purchases = collect($purchases);
    }

    // Month text from $monthYear (e.g. 2025-01 → January 2025)
    $periodText = '';
    if (!empty($monthYear)) {
        $periodDate = strlen($monthYear) === 7 ? Carbon::parse($monthYear . '-01') : Carbon::parse($monthYear);
        $periodText = $periodDate->format('F Y');
    }
@endphp

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Bill of Entry</title>

    @php
        $css = '';

        $manifestPath = public_path('build/manifest.json');
        if (file_exists($manifestPath)) {
            $manifest = json_decode(file_get_contents($manifestPath), true);

            // must match your Vite entry
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
    <div class="card card-body border-custom">
        {{-- HEADER --}}
        <div class="row mb-2">
            <div class="col-12 text-center fw-semibold">
                <h3>Bill of Entry</h3>
                <p class="m-0">{{ $client->company_name ?? '' }}</p>
                <p class="mb-2">{{ $client->bin ?? '' }}</p>
                <p class="m-0 fs-5">{{ $periodText }}</p>
            </div>
        </div>

        {{-- PURCHASE DETAILS --}}
        <div class="card card-body border">
            @if ($purchases->count() > 0)
                <div class="row">
                    <div class="col-12">
                        <p class="m-0 fw-bold bg-info bg-opacity-25 px-1">
                            Purchage Details
                            <span class="text-danger">
                                (Total BoE: {{ $totalBoe ?: $purchases->count() }})
                            </span>
                        </p>
                    </div>

                    <div class="col-12">
                        <div class="table-responsive">
                            <table class="table table-sm table-borderless text-center align-middle">
                                <thead class="border-0 border-bottom border-dark">
                                    <tr>
                                        <th>Note</th>
                                        <th>Station</th>
                                        <th>BoE No</th>
                                        <th>Date</th>
                                        <th>HS Code</th>
                                        <th>Item</th>
                                        <th>Net Quantity</th>
                                        <th>AV</th>
                                        <th>Value</th>
                                        <th>SD</th>
                                        <th>VAT</th>
                                        <th>AT</th>
                                        <th>LC No</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($purchases as $dt)
                                        <tr>
                                            <td>{{ optional($dt->mushaknote)->name_note_no }}</td>
                                            <td>{{ optional($dt->stationcode)->code }}</td>
                                            <td>{{ $dt->boe ?? '' }}</td>
                                            <td>
                                                @if (!empty($dt->boe_date))
                                                    {{ Carbon::parse($dt->boe_date)->format('d/m/Y') }}
                                                @endif
                                            </td>
                                            <td>{{ optional($dt->item)->hs_code }}</td>
                                            <td>{{ optional($dt->item)->name }}</td>
                                            <td>
                                                @php $qty = $dt->total_quantity ?? 0; @endphp
                                                {{ number_format($qty, 2, '.', '') }}
                                                {{ optional($dt->unit)->name }}
                                            </td>
                                            <td>
                                                @php $av = $dt->assessable_value ?? 0; @endphp
                                                {{ number_format($av, 2, '.', '') }}
                                            </td>
                                            <td>
                                                @php $base = $dt->base_value ?? 0; @endphp
                                                {{ number_format($base, 2, '.', '') }}
                                            </td>
                                            <td>
                                                @php $sd = $dt->sd_value ?? 0; @endphp
                                                {{ number_format($sd, 2, '.', '') }}
                                            </td>
                                            <td>
                                                @php $vat = $dt->vat_value ?? 0; @endphp
                                                {{ number_format($vat, 2, '.', '') }}
                                            </td>
                                            <td>
                                                @php $at = $dt->at_value ?? 0; @endphp
                                                {{ number_format($at, 2, '.', '') }}
                                            </td>
                                            <td>{{ $dt->lc_no ?? '' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @else
                <p class="m-0 text-center">No purchase data available for this period.</p>
            @endif
        </div>
    </div>
</body>

</html>
