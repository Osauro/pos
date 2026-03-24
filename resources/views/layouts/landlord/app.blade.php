<!DOCTYPE html>
<html lang="es">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <meta name="description" content="TPV — Panel Landlord" />
    <meta name="robots" content="noindex, nofollow" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin — ' . config('app.name', 'TPV'))</title>

    <!-- Favicon -->
    <link rel="icon" href="{{ asset('assets/images/favicon.png') }}" type="image/x-icon" />
    <link rel="shortcut icon" href="{{ asset('assets/images/favicon.png') }}" type="image/x-icon" />

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:opsz,wght@6..12,200;6..12,300;6..12,400;6..12,500;6..12,600;6..12,700;6..12,800;6..12,900;6..12,1000&display=swap" rel="stylesheet" />

    <!-- Theme assets -->
    <link rel="stylesheet" href="{{ asset('assets/css/vendors/flag-icon.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/iconly-icon.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/bulk-style.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/themify.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/fontawesome-min.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/weather-icons/weather-icons.min.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/scrollbar.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/slick.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/slick-theme.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}" />
    {{-- Landlord usa color-1 (violeta/azul) para diferenciarse del panel de negocios --}}
    <link id="color" rel="stylesheet" href="{{ asset('assets/css/color-1.css') }}" media="screen" />
    <link rel="stylesheet" href="{{ asset('assets/css/custom.css') }}?v={{ filemtime(public_path('assets/css/custom.css')) }}" />

    <style>
        :root {
            --theme-default: #7366ff;
            --primary-color:  #7366ff;
        }
        /* Distinción visual del panel landlord */
        .landlord-badge {
            background: #7366ff;
            color: #fff;
            font-size: 0.65rem;
            padding: 2px 7px;
            border-radius: 20px;
            font-weight: 700;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }
        /* Paginación */
        .active > .page-link, .page-link.active {
            background-color: #7366ff;
            border-color: #7366ff !important;
        }
        .page-link { color: #7366ff !important; }
    </style>

    @livewireStyles
</head>

<body>
    <!-- tap to top -->
    <div class="tap-top"><i class="iconly-Arrow-Up icli"></i></div>

    <!-- loader -->
    <div class="loader-wrapper">
        <div class="loader"><span></span><span></span><span></span><span></span><span></span></div>
    </div>

    <div class="page-wrapper compact-sidebar" id="pageWrapper">

        @include('layouts.landlord.header')

        <div class="page-body-wrapper">

            @include('layouts.landlord.sidebar')

            <div class="page-body">
                {{ $slot }}
            </div>

        </div>
    </div>

    <!-- Scripts -->
    <script src="{{ asset('assets/js/vendors/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/js/vendors/bootstrap/dist/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/js/vendors/bootstrap/dist/js/popper.min.js') }}"></script>
    <script src="{{ asset('assets/js/vendors/font-awesome/fontawesome-min.js') }}"></script>
    <script src="{{ asset('assets/js/vendors/feather-icon/feather.min.js') }}"></script>
    <script src="{{ asset('assets/js/vendors/feather-icon/custom-script.js') }}"></script>
    <script src="{{ asset('assets/js/sidebar.js') }}"></script>
    <script src="{{ asset('assets/js/height-equal.js') }}"></script>
    <script src="{{ asset('assets/js/config.js') }}"></script>
    <script src="{{ asset('assets/js/scrollbar/simplebar.js') }}"></script>
    <script src="{{ asset('assets/js/scrollbar/custom.js') }}"></script>
    <script src="{{ asset('assets/js/slick/slick.min.js') }}"></script>
    <script src="{{ asset('assets/js/slick/slick.js') }}"></script>
    <script src="{{ asset('assets/js/theme-customizer/customizer.js') }}"></script>
    <script src="{{ asset('assets/js/script.js') }}"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('assets/js/custom.js') }}"></script>

    <script>
        $(document).ready(function () {
            localStorage.setItem("color", 'color-1');
            localStorage.setItem("primary", '#7366ff');
            localStorage.setItem("secondary", '#7366ff');
        });
    </script>

    @stack('scripts')

    <script>
        // Ocultar loader cuando la página termine de cargar
        $(window).on('load', function() {
            setTimeout(function() {
                $(".loader-wrapper").fadeOut("slow", function() {
                    $(this).remove();
                });
            }, 300);
        });

        // Fallback: ocultar loader después de 2 segundos si no se ocultó
        setTimeout(function() {
            if ($(".loader-wrapper").is(":visible")) {
                $(".loader-wrapper").fadeOut("fast", function() {
                    $(this).remove();
                });
            }
        }, 2000);
    </script>

    @stack('modals')
    @livewireScripts
</body>

</html>
