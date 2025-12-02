<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}" />

    @inertiaHead
    @routes
</head>

<body class="theme-light">
    @inertia
    <div id="modal-show"></div>
    <div id="toast-show"></div>

    @vite(['resources/js/app.js'])
</body>

</html>
