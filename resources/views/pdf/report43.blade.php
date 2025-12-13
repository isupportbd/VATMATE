@php
    use Carbon\Carbon;

    $client = $view->client ?? null;
    $item = $view->item ?? null;
    $saleunit = $view->saleunit ?? null;

    $submissionDate = $view->submission_date ? Carbon::parse($view->submission_date)->format('d/m/Y') : '';
    $firstDeliveryDate = $view->first_delivery_date ? Carbon::parse($view->first_delivery_date)->format('d/m/Y') : '';
    $boeDate = $view->boe_date ? Carbon::parse($view->boe_date)->format('d/m/Y') : '';

    $wastageQty =
        $view->purchase_unit_total && $view->wastage ? $view->purchase_unit_total * ($view->wastage / 100) : 0;

    $additions = $view->additions ?? [];
    $additionsum = $view->additionsum[0] ?? null;
@endphp

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Mushak-4.3</title>

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
    <div class="container-fluid p-0">
        <div class="row">
            <div class="col-12 text-center position-relative mb-2">
                <img src="{{ resource_path('/assets/images/gov.png') }}" class="fw-bold position-absolute ms-3 start-0"
                    width="60" alt="bd_logo">
                <label class="fw-bold border border-secondary border-1 position-absolute p-1 top-0 me-3 end-0">
                    Mushak-4.3
                </label>
                <p class="m-0">Government of the People's Republic of Bangladesh</p>
                <p class="m-0">National Board of Revenue</p>
                <p class="fw-bold m-0">Mushak-4.3 Material-Product Coefficient (Input-Output Coefficient) Declaration
                </p>
                <p class="m-0">[See Rule 21]</p>
                <p class="m-0">[Please read the instructions before filling up this form]</p>
            </div>

            {{-- PART - 1 --}}
            <div class="col-12 mb-3">
                <div class="table-responsive">
                    <table class="table table-bordered border-secondary align-middle">
                        <colgroup>
                            <col style="width: 39%">
                            <col style="width: 2%">
                            <col style="width: 59%">
                        </colgroup>
                        <thead>
                            <tr class="table-success border-secondary">
                                <th colspan="3">PART - 1: APPLICANT'S INFORMATION</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Name of the Entity</td>
                                <td class="text-center">:</td>
                                <td>{{ $client->company_name ?? '' }}</td>
                            </tr>
                            <tr>
                                <td>Address</td>
                                <td class="text-center">:</td>
                                <td>{{ $client->address ?? '' }}</td>
                            </tr>
                            <tr>
                                <td>BIN</td>
                                <td class="text-center">:</td>
                                <td>{{ $client->bin ?? '' }}</td>
                            </tr>
                            <tr>
                                <td>Date of Submission</td>
                                <td class="text-center">:</td>
                                <td>{{ $submissionDate }}</td>
                            </tr>
                            <tr>
                                <td>Date of First Delivery of Goods /
                                    Services as per Declared Coefficient <span class="text-danger">*</span></td>
                                <td class="text-center">:</td>
                                <td>{{ $firstDeliveryDate }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- PART - 2 --}}
            <div class="col-12 mb-3">
                <div class="table-responsive">
                    <table class="table table-bordered border-secondary align-middle">
                        <colgroup>
                            <col style="width: 39%">
                            <col style="width: 2%">
                            <col style="width: 59%">
                        </colgroup>
                        <thead>
                            <tr class="table-success border-secondary">
                                <th colspan="3">
                                    PART - 2: INFORMATION OF INPUT-OUTPUT COEFFICIENT
                                    <span class="text-danger">*</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>a. Goods HS Code <span class="text-danger">*</span></td>
                                <td class="text-center">:</td>
                                <td>{{ $item->hs_code ?? '' }}</td>
                            </tr>
                            <tr>
                                <td>b. Description of Goods <span class="text-danger">*</span></td>
                                <td class="text-center">:</td>
                                <td>{{ $item->description ?? '' }}</td>
                            </tr>
                            <tr>
                                <td>c. Commercial Description (including Brand Name if applicable)</td>
                                <td class="text-center">:</td>
                                <td>{{ $item->name ?? '' }}</td>
                            </tr>
                            <tr>
                                <td>d. Item Code <span class="text-danger">*</span></td>
                                <td class="text-center">:</td>
                                <td>{{ $view->item_code ?? '' }}</td>
                            </tr>
                            <tr>
                                <td>Unit of Supplies <span class="text-danger">*</span></td>
                                <td class="text-center">:</td>
                                <td>{{ $saleunit->name ?? '' }}</td>
                            </tr>
                            <tr>
                                <td colspan="3">
                                    f. Please click here to fill in the table of “Details of Raw Materials”
                                    <span class="text-danger">*</span>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="3">
                                    g. Please click here to fill in the table of “Details of Value Additions”
                                    <span class="text-danger">*</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- PART - 3 --}}
            <div class="col-12 mb-3">
                <div class="table-responsive">
                    <table class="table table-bordered border-secondary align-middle">
                        <colgroup>
                            <col style="width: 39%">
                            <col style="width: 2%">
                            <col style="width: 59%">
                        </colgroup>
                        <thead>
                            <tr class="table-success border-secondary">
                                <th colspan="3">PART - 3 : DECLARATION <span class="text-danger">*</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Name <span class="text-danger">*</span></td>
                                <td class="text-center">:</td>
                                <td>{{ $client->name ?? '' }}</td>
                            </tr>
                            <tr>
                                <td>Designation <span class="text-danger">*</span></td>
                                <td class="text-center">:</td>
                                <td>Owner</td>
                            </tr>
                            <tr>
                                <td>Mobile Number <span class="text-danger">*</span></td>
                                <td class="text-center">:</td>
                                <td>{{ $client->mobile ?? '' }}</td>
                            </tr>
                            <tr>
                                <td>National ID/Passport Number <span class="text-danger">*</span></td>
                                <td class="text-center">:</td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>Email <span class="text-danger">*</span></td>
                                <td class="text-center">:</td>
                                <td>{{ $client->email ?? '' }}</td>
                            </tr>
                            <tr>
                                <td colspan="3">
                                    <div class="d-flex align-items-center">
                                        <input type="checkbox" checked>
                                        <p class="m-0 ms-3">
                                            <span class="text-danger">*</span>
                                            I hereby declare that all information provided in this Refund
                                            Form are complete, true &amp; accurate. In case of any untrue/incomplete
                                            statement, I may be subjected
                                            to penal action under The Value Added Tax and Supplementary Duty Act, 2012
                                            or any other applicable
                                            Act prevailing at present.
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Special Notes --}}
            <div class="col-12 mb-2">
                <div class="border border-secondary p-2">
                    <p>Special Notes:</p>
                    <p>1. Within 15 working days prior to the first delivery of any product or service, the
                        declaration of materials-production co-declaration must be submitted online to the VAT computer
                        system
                        or to the office of the concerned departmental officer.</p>
                    <p>2. If the price of the product or the total material / raw material changes by more than
                        7.5%, a new declaration has to be submitted.</p>
                    <p class="m-0">3. A copy of the bill of entry or invoice should be attached as an authentic
                        document in
                        support of the purchase of materials.</p>
                </div>
            </div>

            {{-- Sub-form f: Details of Raw Materials --}}
            <div class="col-12 mb-2">
                <h6>Sub-form of PART - 2: f. Details of Raw Materials</h6>
                <div class="table-responsive">
                    <table class="table table-bordered border-secondary text-center">
                        <thead class="align-middle">
                            <tr class="table-success border-secondary text-center">
                                <th>Goods/ Service Code <span class="text-danger">*</span></th>
                                <th>Description of Goods/Service <span class="text-danger">*</span></th>
                                <th>Commercial Description (including Brand Name if applicable)</th>
                                <th>Unit of Supplies <span class="text-danger">*</span></th>
                                <th>Taxpayer - Product Description <span class="text-danger">*</span></th>
                                <th>Taxpayer - Per Unit Product Qty. including Wastage <span
                                        class="text-danger">*</span></th>
                                <th>Taxpayer - Per Unit Product Purchase Price <span class="text-danger">*</span></th>
                                <th>Taxpayer - Per Unit Product Qty. of Wastage <span class="text-danger">*</span></th>
                                <th>Taxpayer - Per Unit Product Wastage Percentage (%) <span
                                        class="text-danger">*</span></th>
                                <th>Taxpayer - Notes</th>
                            </tr>
                        </thead>
                        <tbody class="align-middle">
                            <tr>
                                <td>{{ $item->hs_code ?? '' }}</td>
                                <td>{{ $item->description ?? '' }}</td>
                                <td>{{ $item->name ?? '' }}</td>
                                <td>{{ $saleunit->name ?? '' }}</td>
                                <td>{{ $item->name ?? '' }}</td>
                                <td>{{ number_format($view->purchase_unit_total, 2, '.', '') ?? '' }}</td>
                                <td>{{ number_format($view->unit_price, 2, '.', '') ?? '' }}</td>
                                <td>{{ number_format($wastageQty, 2, '.', '') }}</td>
                                <td>{{ number_format($view->wastage, 2, '.', '') ?? '' }}%</td>
                                <td>
                                    <p class="m-0">BoE: {{ $view->boe_no ?? '' }}</p>
                                    <p class="m-0">Date: {{ $boeDate }}</p>
                                </td>
                            </tr>
                            <tr>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td>Total</td>
                                <td></td>
                                <td></td>
                                <td>{{ $view->unit_price ?? '' }}</td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Divisional Officer Observation (Raw) --}}
            <div class="col-12 mb-2">
                <h6>Divisional Officer Observation:</h6>
                <div class="table-responsive">
                    <table class="table table-bordered border-secondary text-center">
                        <thead class="align-middle">
                            <tr class="table-success border-secondary text-center">
                                <th>Goods/ Service Code <span class="text-danger">*</span></th>
                                <th>Description of Goods/Service <span class="text-danger">*</span></th>
                                <th>Commercial Description (including Brand Name if applicable)</th>
                                <th>Unit of Supplies <span class="text-danger">*</span></th>
                                <th>Divisional Officer - Status <span class="text-danger">*</span></th>
                                <th>Divisional Officer - Product Description <span class="text-danger">*</span></th>
                                <th>Divisional Officer - Per Unit Product Qty. including Wastage <span
                                        class="text-danger">*</span></th>
                                <th>Divisional Officer - Per Unit Product Purchase Price <span
                                        class="text-danger">*</span></th>
                                <th>Divisional Officer - Per Unit Product Qty. of Wastage <span
                                        class="text-danger">*</span></th>
                                <th>Divisional Officer - Per Unit Product Wastage Percentage (%) <span
                                        class="text-danger">*</span></th>
                                <th>Divisional Officer - Notes</th>
                            </tr>
                        </thead>
                        <tbody class="align-middle">
                            <tr>
                                <td>&nbsp;</td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td>Total</td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Commissioner Observation (Raw) --}}
            <div class="col-12 mb-2">
                <h6>Commissioner Observation:</h6>
                <div class="table-responsive">
                    <table class="table table-bordered border-secondary text-center">
                        <thead class="align-middle">
                            <tr class="table-success border-secondary text-center">
                                <th>Goods/ Service Code <span class="text-danger">*</span></th>
                                <th>Description of Goods/Service <span class="text-danger">*</span></th>
                                <th>Commercial Description (including Brand Name if applicable)</th>
                                <th>Unit of Supplies <span class="text-danger">*</span></th>
                                <th>Commission er - Status <span class="text-danger">*</span></th>
                                <th>Commission er - Product Description <span class="text-danger">*</span></th>
                                <th>Commission er - Per Unit Product Qty. including Wastage <span
                                        class="text-danger">*</span></th>
                                <th>Commission er - Per Unit Product Purchase Price <span class="text-danger">*</span>
                                </th>
                                <th>Commission er - Per Unit Product Qty. of Wastage <span class="text-danger">*</span>
                                </th>
                                <th>Commission er - Per Unit Product Wastage Percentage (%) <span
                                        class="text-danger">*</span></th>
                                <th>Commission er - Notes</th>
                            </tr>
                        </thead>
                        <tbody class="align-middle">
                            <tr>
                                <td>&nbsp;</td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td>Total</td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Sub-form g: Value Additions --}}
            <div class="col-12 mb-2">
                <h6>Sub-form of PART - 2: g. Details of Value Additions</h6>
                <div class="table-responsive">
                    <table class="table table-bordered border-secondary align-middle">
                        <thead>
                            <tr class="table-success border-secondary">
                                <th>Taxpayer - Value Addition Items <span class="text-danger">*</span>
                                </th>
                                <th>Taxpayer - Value <span class="text-danger">*</span></th>
                                <th>Taxpayer - Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($additions as $dt)
                                <tr>
                                    <td>{{ $dt->costsheet->name ?? '' }}</td>
                                    <td>{{ $dt->value ?? '' }}</td>
                                    <td>{{ $dt->rate ?? '' }}%</td>
                                </tr>
                            @endforeach
                            <tr>
                                <td>Total</td>
                                <td>{{ $additionsum->valuesum ?? '' }}</td>
                                <td>{{ isset($additionsum->ratesum) ? $additionsum->ratesum . '%' : '' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Divisional Officer Observation (Value Additions) --}}
            <div class="col-12 mb-2">
                <h6>Divisional Officer Observation:</h6>
                <div class="table-responsive">
                    <table class="table table-bordered border-secondary">
                        <thead class="align-middle">
                            <tr class="table-success border-secondary">
                                <th>Divisional Officer - Status <span class="text-danger">*</span></th>
                                <th>Divisional Officer - Value Addition Items <span class="text-danger">*</span></th>
                                <th>Divisional Officer - Value <span class="text-danger">*</span></th>
                                <th>Divisional Officer - Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>&nbsp;</td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td></td>
                                <td>Total</td>
                                <td></td>
                                <td></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Commissioner Observation (Value Additions) --}}
            <div class="col-12">
                <h6>Commissioner Observation:</h6>
                <div class="table-responsive">
                    <table class="table table-bordered border-secondary">
                        <thead class="align-middle">
                            <tr class="table-success border-secondary">
                                <th>Commissioner - Status <span class="text-danger">*</span></th>
                                <th>Commissioner - Value Addition Items <span class="text-danger">*</span></th>
                                <th>Commissioner - Value <span class="text-danger">*</span></th>
                                <th>Commissioner - Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>&nbsp;</td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td></td>
                                <td>Total</td>
                                <td></td>
                                <td></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</body>

</html>
