<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Nueva Tienda — {{ config('app.name', 'POS') }}</title>

    <link rel="icon" href="{{ asset('assets/images/favicon.png') }}" type="image/x-icon" />

    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:opsz,wght@6..12,400;6..12,600;6..12,700;6..12,800&display=swap" rel="stylesheet" />

    @php
        $themeNumber = currentTenant()?->theme_number ?? 3;
        $themeColor  = currentTenant()?->themeColor() ?? '#29adb2';
    @endphp

    <link rel="stylesheet" href="{{ asset('assets/css/fontawesome-min.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/color-' . $themeNumber . '.css') }}" />

    <style>
        :root {
            --theme-default: {{ $themeColor }};
            --primary-color: {{ $themeColor }};
        }
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(0,0,0,0.6);
            font-family: 'Nunito Sans', sans-serif;
        }
    </style>

    @livewireStyles
</head>
<body>

    {{ $slot }}

    <script src="{{ asset('assets/js/vendors/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/js/vendors/bootstrap/dist/js/bootstrap.bundle.min.js') }}"></script>

    @livewireScripts

</body>
</html>
