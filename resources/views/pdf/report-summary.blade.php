{{-- resources/views/report-summary-pdf.blade.php --}}

@php
    use Carbon\Carbon;

    /** @var object $summary */
    $summary = (object) $summary ?? null;
    $client = $summary?->client;

    $monthYear = $monthYear ?? request('monthYear'); // expect "YYYY-MM"
    try {
        $taxPeriod = $monthYear ? Carbon::createFromFormat('Y-m', $monthYear)->format('F Y') : '';
    } catch (\Exception $e) {
        $taxPeriod = $monthYear ?? '';
    }

    $sale = $summary?->sale ?? [];
    $saleSum = $summary?->saleSum ?? null;
    $purchase = $summary?->purchase ?? [];
    $purchaseSum = $summary?->purchaseSum ?? null;
    $purchaseDetails = $summary?->purchaseDetails ?? [];
@endphp

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Summary by BIN</title>

    {{-- 🔗 Load compiled app.css from Vite (same as your other PDFs) --}}
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
            font-size: 12px;
            margin: 15px;
        }
    </style>
</head>

<body>

    {{-- ===================== --}}
    {{--    HEADER SUMMARY     --}}
    {{-- ===================== --}}
    <div class="card card-body border mb-2 p-5 pp-0">
        <div class="row mb-3">
            <div class="col-12">
                <h3 class="text-center">SUMMARY BY BIN</h3>
            </div>
        </div>

        <div class="row mb-2">
            <div class="col-12 col-lg-9 col-xl-10">
                <p class="m-0">
                    <strong>Tax Period : </strong>
                    {{ $taxPeriod }}
                </p>
                <p class="m-0">
                    <strong>BIN : </strong>
                    {{ $client->bin ?? '' }}
                </p>
                @if (!empty($client?->tin))
                    <p class="m-0">
                        <strong>TIN : </strong>
                        {{ $client->tin }}
                    </p>
                @endif
                <p class="m-0">
                    <strong>Name : </strong>
                    {{ $client->company_name ?? '' }}
                </p>
                <p class="m-0">
                    <strong>User : </strong>
                    {{ $client->vat_username ?? '' }}
                </p>
                <p class="m-0">
                    <strong>Pass : </strong>
                    {{ $client->vat_password ?? '' }}
                </p>
                @if (!empty($client?->circle))
                    <p class="m-0">
                        <strong>Circle : </strong>
                        {{ $client->circle }}
                    </p>
                @endif
            </div>
        </div>

        {{-- ===================== --}}
        {{--     SALE SUMMARY      --}}
        {{-- ===================== --}}
        @if (count($sale) > 0)
            <div class="row mb-2">
                <div class="col-12">
                    <p class="fw-bold mb-1 bg-info bg-opacity-25 px-2">Sale Summary</p>
                    <div class="table-responsive">
                        <table class="table table-sm table-borderless text-center align-middle">
                            <thead class="border-0 border-bottom border-secondary">
                                <tr>
                                    <th>#</th>
                                    <th>VAT Rate</th>
                                    <th>HS Code</th>
                                    <th>Item</th>
                                    <th>Quantity</th>
                                    <th>Value</th>
                                    <th>SD</th>
                                    <th>VAT</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($sale as $i => $dt)
                                    <tr>
                                        <td>{{ $i + 1 }}</td>
                                        <td>{{ $dt->vat_rate ?? 0 }}%</td>
                                        <td>{{ $dt->item->hs_code ?? '' }}</td>
                                        <td>{{ $dt->item->name ?? '' }}</td>
                                        <td>
                                            {{ $dt->quantity ?? 0 }}
                                            {{ $dt->unit->name ?? '' }}
                                        </td>
                                        <td>{{ $dt->item_price ?? 0 }}</td>
                                        <td>{{ $dt->sd_value ?? 0 }}</td>
                                        <td>{{ $dt->vat_value ?? 0 }}</td>
                                    </tr>
                                @endforeach
                                <tr class="border-0 border-top border-secondary">
                                    <td colspan="5" class="text-end">Total = </td>
                                    <td>{{ $saleSum->item_price_sum ?? 0 }}</td>
                                    <td>{{ $saleSum->sd_value_sum ?? 0 }}</td>
                                    <td>{{ $saleSum->vat_value_sum ?? 0 }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        {{-- ======================== --}}
        {{--    PURCHASE SUMMARY      --}}
        {{-- ======================== --}}
        @if (count($purchase) > 0)
            <div class="row mb-2">
                <div class="col-12">
                    <p class="fw-bold mb-1 bg-info bg-opacity-25 px-2">Purchase Summary</p>
                    <div class="table-responsive">
                        <table class="table table-sm table-borderless text-center align-middle">
                            <thead class="border-0 border-bottom border-secondary">
                                <tr>
                                    <th>#</th>
                                    <th>Note</th>
                                    <th>HS Code</th>
                                    <th>Item</th>
                                    <th>Net Quantity</th>
                                    <th>AV</th>
                                    <th>Base value of VAT</th>
                                    <th>SD</th>
                                    <th>VAT</th>
                                    <th>AT</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($purchase as $i => $dt)
                                    <tr>
                                        <td>{{ $i + 1 }}</td>
                                        <td>{{ $dt->mushaknote->name_note_no ?? '' }}</td>
                                        <td>{{ $dt->item->hs_code ?? '' }}</td>
                                        <td>{{ $dt->item->name ?? '' }}</td>
                                        <td>
                                            {{ $dt->total_quantity ?? 0 }}
                                            {{ $dt->unit->name ?? '' }}
                                        </td>
                                        <td>{{ $dt->assessable_value ?? 0 }}</td>
                                        <td>{{ $dt->base_value ?? 0 }}</td>
                                        <td>{{ $dt->sd_value ?? 0 }}</td>
                                        <td>{{ $dt->vat_value ?? 0 }}</td>
                                        <td>{{ $dt->at_value ?? 0 }}</td>
                                    </tr>
                                @endforeach
                                <tr class="border-0 border-top border-secondary">
                                    <td colspan="5" class="text-end">Total = </td>
                                    <td>{{ $purchaseSum->assessable_value_sum ?? 0 }}</td>
                                    <td>{{ $purchaseSum->base_value_sum ?? 0 }}</td>
                                    <td>{{ $purchaseSum->sd_value_sum ?? 0 }}</td>
                                    <td>{{ $purchaseSum->vat_value_sum ?? 0 }}</td>
                                    <td>{{ $purchaseSum->at_value_sum ?? 0 }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        {{-- ========================= --}}
        {{--   TRANSACTION SUMMARY     --}}
        {{-- ========================= --}}
        <div class="row mb-2">
            <div class="col-12">
                <p class="fw-bold mb-1 bg-info bg-opacity-25 px-2">Transaction Summary</p>
                <div class="row gy-3">
                    {{-- If you have adjustments array, you can loop; for now we stick to fixed items below --}}

                    <div class="col-12 col-sm-3">
                        <span class="fw-bold">Treasury : </span>
                        <span>{{ $summary?->treasury?->amount ?? 0 }}</span>
                    </div>

                    <div class="col-12 col-sm-3">
                        <span class="fw-bold">Note 52 : </span>
                        <span>{{ $summary?->prevNetPayable ?? 0 }}</span>
                    </div>

                    <div class="col-12 col-sm-3">
                        <span class="fw-bold">Net Payable VAT : </span>
                        <span>{{ $summary?->netPayableRegular ?? 0 }}</span>
                    </div>

                    <div class="col-12 col-sm-3">
                        <span class="fw-bold">Note 65 : </span>
                        <span>{{ $summary?->closignBalanceCurrentRegular ?? 0 }}</span>
                    </div>

                    <div class="col-12 col-sm-3">
                        <span class="fw-bold">MCU : </span>
                        <span>{{ $summary?->totalMcu ?? 0 }}</span>
                    </div>

                    <div class="col-12 col-sm-3">
                        <span class="fw-bold">Debit Note : </span>
                        <span>{{ $summary?->totalDebitnote?->increase_vat ?? 0 }}</span>
                    </div>

                    <div class="col-12 col-sm-3">
                        <span class="fw-bold">Credit Note : </span>
                        <span>{{ $summary?->totalCreditnote?->decrease_vat ?? 0 }}</span>
                    </div>

                    <div class="col-12 col-sm-3">
                        <span class="fw-bold">Mushak 4.4 : </span>
                        <span>{{ $summary?->totalForm44?->increase_vat ?? 0 }}</span>
                    </div>

                    <div class="col-12 col-sm-3">
                        <span class="fw-bold">Mushak 4.5 : </span>
                        <span>{{ $summary?->totalForm45?->increase_vat ?? 0 }}</span>
                    </div>

                    <div class="col-12 col-sm-3">
                        <span class="fw-bold">Note 28 : </span>
                        <span>{{ $summary?->totalIncAdj ?? 0 }}</span>
                    </div>

                    <div class="col-12 col-sm-3">
                        <span class="fw-bold">Note 33 : </span>
                        <span>{{ $summary?->totalDecAdj ?? 0 }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ========================= --}}
    {{--   PURCHASE DETAILS       --}}
    {{-- ========================= --}}
    <div class="card card-body border" style="margin-top: 15% !important;">
        @if (count($purchaseDetails) > 0)
            <div class="row">
                <div class="col-12">
                    <p class="m-0 px-2 fw-bold bg-info bg-opacity-25">
                        Purchage Details
                        <span class="text-danger">
                            (Total BoE: {{ $summary?->totalBoe ?? 0 }})
                        </span>
                    </p>
                </div>
                <div class="col-12">
                    <div class="table-responsive">
                        <table class="table table-sm table-borderless text-center align-middle">
                            <thead class="border-0 border-bottom border-secondary">
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
                                @foreach ($purchaseDetails as $dt)
                                    @php
                                        $boeDate = $dt->boe_date ? Carbon::parse($dt->boe_date)->format('d/m/Y') : '';
                                    @endphp
                                    <tr>
                                        <td>{{ $dt->mushaknote->name_note_no ?? '' }}</td>
                                        <td>{{ $dt->stationcode->code ?? '' }}</td>
                                        <td>{{ $dt->boe ?? '' }}</td>
                                        <td>{{ $boeDate }}</td>
                                        <td>{{ $dt->item->hs_code ?? '' }}</td>
                                        <td>{{ $dt->item->name ?? '' }}</td>
                                        <td>
                                            {{ $dt->total_quantity ?? 0 }}
                                            {{ $dt->unit->name ?? '' }}
                                        </td>
                                        <td>{{ number_format((float) ($dt->assessable_value ?? 0), 2) }}</td>
                                        <td>{{ number_format((float) ($dt->base_value ?? 0), 2) }}</td>
                                        <td>{{ number_format((float) ($dt->sd_value ?? 0), 2) }}</td>
                                        <td>{{ number_format((float) ($dt->vat_value ?? 0), 2) }}</td>
                                        <td>{{ number_format((float) ($dt->at_value ?? 0), 2) }}</td>
                                        <td>{{ $dt->lc_no ?? '' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    </div>

</body>

</html>
