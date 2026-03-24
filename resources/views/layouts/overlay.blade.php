<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ currentTenant()?->nombre ?? config('app.name', 'POS') }}</title>

    <link rel="icon" href="{{ asset('assets/images/favicon.png') }}" type="image/x-icon" />

    @php
        $themeNumber = currentTenant()?->theme_number ?? 3;
        $themeColor  = currentTenant()?->themeColor() ?? '#29adb2';
    @endphp

    <link rel="stylesheet" href="{{ asset('assets/css/fontawesome-min.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/color-' . $themeNumber . '.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}" />

    <style>
        :root {
            --theme-default: {{ $themeColor }};
            --primary-color: {{ $themeColor }};
        }
        body { margin: 0; padding: 0; font-family: 'Nunito Sans', sans-serif; background: rgba(0,0,0,0.82); min-height: 100vh; }
    </style>

    @livewireStyles
</head>
<body>

    {{ $slot }}

    @livewireScripts
</body>
</html>
