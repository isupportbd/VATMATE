{{-- resources/views/report43-forwarding.blade.php --}}

@php
    use Carbon\Carbon;

    /** @var \App\Models\Form43 $report43 */

    $client = $view->client ?? null;
    $division = $client->division ?? null;

    // Use Mushak date if you have one on view, otherwise today
    $date = $view->date ? Carbon::parse($view->date)->format('d/m/Y') : Carbon::now()->format('d/m/Y');
@endphp

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>4.3 Forwarding</title>

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
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                {{-- Header: Client info --}}
                <div class="fw-bold text-center">
                    <h5 class="m-0">
                        {{ $client->company_name ?? '' }}
                    </h5>
                    <p class="m-0">
                        {{ $client->address ?? '' }}
                    </p>
                    <p class="m-0">
                        BIN: {{ $client->bin ?? '' }}
                    </p>
                    <p class="m-0">
                        Mobile: {{ $client->mobile ?? '' }}
                    </p>
                </div>

                {{-- Ref & Date --}}
                <div class="row my-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <span class="m-0">Ref:</span>
                        <span class="m-0">Date: {{ $date }}</span>
                    </div>
                </div>

                {{-- Address + Subject + Body --}}
                <div class="row">
                    <div class="col-12">
                        <p class="m-0">The Divisional Officer</p>
                        <p class="m-0">
                            Customs, Excise &amp; VAT Division,
                            {{ $division->name ?? '' }}
                        </p>
                    </div>

                    <div class="col-12 my-3">
                        <p class="fw-bold">
                            Subject: <span class="text-decoration-underline">Submission of Input-Output Coefficient
                                (Mushak-4.3) declaration.</span>
                        </p>
                    </div>

                    <div class="col-12">
                        <p class="text-justify">
                            Dear Sir, <br />
                            We are pleased to enclose herewith the following documents
                            relating to the Input-Output Coefficient declaration of the
                            products under section 32(5) of the VAT &amp; SD Act, 2012 and
                            under rule 21 of the VAT &amp; SD Rules, 2016 for your kind
                            approval.
                            <br /><br />
                            Please acknowledge receipt.
                            <br /><br />
                            Sincerely Yours,
                            <br /><br /><br /><br /><br /><br />
                            <span class="border-top border-dark">Authorized Person</span><br />
                        </p>
                    </div>

                    {{-- Enclosure list --}}
                    <div class="col-12 mt-3">
                        <span class="border-bottom border-dark">Enclosure: </span><br />
                        <span>1) Form "Mushak-4.3" duly filled &amp; signed</span><br />
                        <span>2) Bill of Entry for imported materials</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
