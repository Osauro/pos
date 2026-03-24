<!DOCTYPE html>
<html lang="es">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description" content="TPV — Sistema de Punto de Venta" />
    <meta name="robots" content="noindex, nofollow" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ currentTenant()?->nombre ?? config('app.name', 'POS') }}</title>

    <!-- PWA -->
    <link rel="manifest" href="{{ route('pwa.manifest') }}" />
    <meta name="mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-status-bar-style" content="default" />
    <meta name="apple-mobile-web-app-title" content="{{ currentTenant()?->nombre ?? config('app.name') }}" />
    <meta name="theme-color" content="{{ currentTenant()?->themeColor() ?? '#29adb2' }}" />
    <link rel="apple-touch-icon" href="{{ asset('assets/images/icon-192.png') }}" />

    <!-- Favicon -->
    <link rel="icon" href="{{ asset('assets/images/favicon.png') }}" type="image/x-icon" />
    <link rel="shortcut icon" href="{{ asset('assets/images/favicon.png') }}" type="image/x-icon" />

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:opsz,wght@6..12,200;6..12,300;6..12,400;6..12,500;6..12,600;6..12,700;6..12,800;6..12,900;6..12,1000&display=swap" rel="stylesheet" />

    <!-- Flag icon css -->
    <link rel="stylesheet" href="{{ asset('assets/css/vendors/flag-icon.css') }}" />
    <!-- iconly-icon-->
    <link rel="stylesheet" href="{{ asset('assets/css/iconly-icon.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/bulk-style.css') }}" />
    <!-- iconly-icon-->
    <link rel="stylesheet" href="{{ asset('assets/css/themify.css') }}" />
    <!--fontawesome-->
    <link rel="stylesheet" href="{{ asset('assets/css/fontawesome-min.css') }}" />
    <!-- Weather Icon css-->
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/weather-icons/weather-icons.min.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/scrollbar.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/slick.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/vendors/slick-theme.css') }}" />
    <!-- App css -->
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}" />

    @php
        $currentTenant  = currentTenant();
        $themeNumber    = $currentTenant?->theme_number ?? 3;
        $themeColor     = $currentTenant?->themeColor() ?? '#29adb2';
    @endphp

    <link id="color" rel="stylesheet" href="{{ asset('assets/css/color-' . $themeNumber . '.css') }}?v={{ time() }}" media="screen" />

    <style>
        :root {
            --theme-default: {{ $themeColor }};
            --primary-color: {{ $themeColor }};
            --livewire-progress-bar-color: {{ $themeColor }} !important;
        }

        /* Fix para backdrop y modales de Bootstrap */
        .modal-backdrop {
            z-index: 1055 !important;
        }
        .modal {
            z-index: 1056 !important;
        }

        .modal.fade.show.d-block {
            z-index: 1056 !important;
        }

        .modal.show {
            z-index: 1056 !important;
        }

        body > .modal-backdrop {
            z-index: 1055 !important;
        }
    </style>

    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/custom.css') }}?v={{ filemtime(public_path('assets/css/custom.css')) }}" />

    @livewireStyles
</head>

<body>
    <!-- tap on top starts-->
    <div class="tap-top"><i class="iconly-Arrow-Up icli"></i></div>
    <!-- tap on tap ends-->

    <!-- loader-->
    <div class="loader-wrapper">
        <div class="loader"><span></span><span></span><span></span><span></span><span></span></div>
    </div>

    <div class="page-wrapper compact-wrapper" id="pageWrapper">

        @include('layouts.theme.header')

        <!-- Page Body Start-->
        <div class="page-body-wrapper">

            <!-- Page sidebar start-->
            @include('layouts.theme.sidebar')
            <!-- Page sidebar end-->

            <div class="page-body" @if(request()->routeIs('crear-tienda')) style="margin-left:0 !important; min-height:100vh; display:flex; align-items:center; justify-content:center; background:rgba(0,0,0,0.5);" @endif>
                {{ $slot }}

                <!-- Espaciador para vista móvil (barra inferior fija) -->
                <div class="d-md-none" style="min-height: 70px;"></div>
            </div>

        </div>

    </div>

    <!-- jquery-->
    <script src="{{ asset('assets/js/vendors/jquery/jquery.min.js') }}"></script>
    <!-- bootstrap js-->
    <script src="{{ asset('assets/js/vendors/bootstrap/dist/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/js/vendors/bootstrap/dist/js/popper.min.js') }}"></script>
    <!--fontawesome-->
    <script src="{{ asset('assets/js/vendors/font-awesome/fontawesome-min.js') }}"></script>
    <!-- feather-->
    <script src="{{ asset('assets/js/vendors/feather-icon/feather.min.js') }}"></script>
    <script src="{{ asset('assets/js/vendors/feather-icon/custom-script.js') }}"></script>
    <!-- sidebar -->
    <script src="{{ asset('assets/js/sidebar.js') }}"></script>
    <!-- height_equal-->
    <script src="{{ asset('assets/js/height-equal.js') }}"></script>
    <!-- config-->
    <script src="{{ asset('assets/js/config.js') }}"></script>
    <!-- apex-->
    <script src="{{ asset('assets/js/chart/apex-chart/apex-chart.js') }}"></script>
    <script src="{{ asset('assets/js/chart/apex-chart/stock-prices.js') }}"></script>
    <!-- scrollbar-->
    <script src="{{ asset('assets/js/scrollbar/simplebar.js') }}"></script>
    <script src="{{ asset('assets/js/scrollbar/custom.js') }}"></script>
    <!-- slick-->
    <script src="{{ asset('assets/js/slick/slick.min.js') }}"></script>
    <script src="{{ asset('assets/js/slick/slick.js') }}"></script>
    <!-- theme_customizer-->
    <script src="{{ asset('assets/js/theme-customizer/customizer.js') }}"></script>
    <!-- tilt-->
    <script src="{{ asset('assets/js/animation/tilt/tilt.jquery.js') }}"></script>
    <!-- page_tilt-->
    <script src="{{ asset('assets/js/animation/tilt/tilt-custom.js') }}"></script>
    <!-- custom script -->
    <script src="{{ asset('assets/js/script.js') }}"></script>
    <script src="{{ asset('assets/js/toasts-custom.js') }}"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Custom JS -->
    <script src="{{ asset('assets/js/custom.js') }}"></script>

    <script>
        function toast(mensaje, tipo = 'success') {
            // creamos el contenedor si no existe
            let toastContainer = document.getElementById('toastContainer');
            if (!toastContainer) {
                toastContainer = document.createElement('div');
                toastContainer.id = 'toastContainer';
                toastContainer.className = 'position-fixed bottom-0 end-0 p-3';
                toastContainer.style.zIndex = '11';
                document.body.appendChild(toastContainer);
            }

            // creamos el elemento html toast
            const toastHTML = `
                <div class="toast" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="toast-header bg-${tipo} text-white">
                        <strong class="me-auto">Notificación</strong>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                    <div class="toast-body">
                        ${mensaje}
                    </div>
                </div>
            `;

            // agregamos el toast al contenedor
            toastContainer.insertAdjacentHTML('beforeend', toastHTML);

            // inicializamos y mostramos el toast
            const toastElement = toastContainer.lastElementChild;
            const toast = new bootstrap.Toast(toastElement);
            toast.show();

            // eliminamos el toast del DOM después de ocultarlo
            toastElement.addEventListener('hidden.bs.toast', () => {
                toastElement.remove();
            });
        }

        $(document).ready(function () {
            localStorage.setItem("color", 'color-{{ $themeNumber }}');
            localStorage.setItem("primary", '{{ $themeColor }}');
            localStorage.setItem("secondary", '{{ $themeColor }}');
        });

        // Restringir fechas futuras en todos los inputs de tipo date - GLOBAL
        (function() {
            const getToday = () => new Date().toISOString().split('T')[0];

            function restrictFutureDates() {
                const today = getToday();
                document.querySelectorAll('input[type="date"]').forEach(input => {
                    input.setAttribute('max', today);

                    if (input.value && input.value > today) {
                        input.value = today;
                    }

                    if (!input.dataset.maxDateRestricted) {
                        input.dataset.maxDateRestricted = 'true';

                        input.addEventListener('input', function(e) {
                            const currentToday = getToday();
                            this.setAttribute('max', currentToday);
                            if (this.value > currentToday) {
                                this.value = currentToday;
                            }
                        });

                        input.addEventListener('change', function(e) {
                            const currentToday = getToday();
                            this.setAttribute('max', currentToday);
                            if (this.value > currentToday) {
                                this.value = currentToday;
                            }
                        });
                    }
                });
            }

            restrictFutureDates();

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', restrictFutureDates);
            } else {
                setTimeout(restrictFutureDates, 100);
            }

            const observer = new MutationObserver(function(mutations) {
                let shouldRestrict = false;
                mutations.forEach(function(mutation) {
                    if (mutation.addedNodes.length) {
                        mutation.addedNodes.forEach(node => {
                            if (node.nodeType === 1) {
                                if (node.tagName === 'INPUT' && node.type === 'date') {
                                    shouldRestrict = true;
                                } else if (node.querySelectorAll) {
                                    if (node.querySelectorAll('input[type="date"]').length > 0) {
                                        shouldRestrict = true;
                                    }
                                }
                            }
                        });
                    }
                });
                if (shouldRestrict) {
                    restrictFutureDates();
                }
            });

            observer.observe(document.body || document.documentElement, {
                childList: true,
                subtree: true
            });

            if (typeof Livewire !== 'undefined') {
                Livewire.hook('commit', ({ component, commit, respond, succeed, fail }) => {
                    succeed(() => {
                        setTimeout(restrictFutureDates, 50);
                    });
                });

                Livewire.hook('morph.updated', ({ el, component }) => {
                    setTimeout(restrictFutureDates, 50);
                });
            }

            document.addEventListener('livewire:load', () => setTimeout(restrictFutureDates, 100));
            document.addEventListener('livewire:update', () => setTimeout(restrictFutureDates, 50));
            document.addEventListener('livewire:navigated', () => setTimeout(restrictFutureDates, 100));

            setInterval(restrictFutureDates, 1000);
        })();
    </script>

    @stack('scripts')

    <!-- PWA: Banner de instalación -->
    <div id="pwa-install-banner" style="
        display: none;
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        z-index: 9999;
        background: #fff;
        border-top: 3px solid #29adb2;
        box-shadow: 0 -4px 20px rgba(0,0,0,.15);
        padding: 14px 16px;
        align-items: center;
        gap: 12px;
        font-family: inherit;
    ">
        <img src="{{ asset('assets/images/icon-192.png') }}" alt="TPV"
             style="width:48px; height:48px; border-radius:10px; flex-shrink:0;" />
        <div style="flex:1; min-width:0;">
            <div style="font-weight:700; font-size:15px; color:#333; line-height:1.2;">
                Instalar TPV
            </div>
            <div style="font-size:12px; color:#666; margin-top:2px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                Acceso rápido desde tu pantalla de inicio
            </div>
        </div>
        <button id="pwa-btn-install" style="
            background: #29adb2;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 8px 18px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            flex-shrink: 0;
            white-space: nowrap;
        ">Instalar</button>
        <button id="pwa-btn-dismiss" style="
            background: none;
            border: none;
            color: #999;
            font-size: 22px;
            line-height: 1;
            cursor: pointer;
            padding: 0 4px;
            flex-shrink: 0;
        " title="Cerrar">&times;</button>
    </div>

    <script>
        (function () {
            const STORAGE_KEY = 'pwa_install_decision';
            // Valores posibles: 'accepted' | 'dismissed'
            // Si está en 'dismissed', no mostramos el banner a menos que hayan pasado 7 días
            const DISMISSED_TTL_DAYS = 7;

            let deferredPrompt = null;
            const banner = document.getElementById('pwa-install-banner');
            const btnInstall = document.getElementById('pwa-btn-install');
            const btnDismiss = document.getElementById('pwa-btn-dismiss');

            function shouldShow() {
                try {
                    const raw = localStorage.getItem(STORAGE_KEY);
                    if (!raw) return true;
                    const data = JSON.parse(raw);
                    if (data.decision === 'accepted') return false;
                    if (data.decision === 'dismissed') {
                        const daysSince = (Date.now() - data.ts) / (1000 * 60 * 60 * 24);
                        return daysSince >= DISMISSED_TTL_DAYS;
                    }
                } catch (e) { /* ignorar */ }
                return true;
            }

            function saveDecision(decision) {
                try {
                    localStorage.setItem(STORAGE_KEY, JSON.stringify({ decision, ts: Date.now() }));
                } catch (e) { /* ignorar */ }
            }

            function showBanner() {
                banner.style.display = 'flex';
                // En móvil, separar del contenido inferior si la barra ya existe
                const spacer = document.querySelector('.d-md-none[style*="min-height"]');
                if (spacer) spacer.style.minHeight = '140px';
            }

            function hideBanner() {
                banner.style.display = 'none';
                const spacer = document.querySelector('.d-md-none[style*="min-height"]');
                if (spacer) spacer.style.minHeight = '70px';
            }

            // Registrar Service Worker
            if ('serviceWorker' in navigator) {
                window.addEventListener('load', () => {
                    navigator.serviceWorker.register('/sw.js').catch(() => {});
                });
            }

            // Capturar el evento de instalación del navegador
            window.addEventListener('beforeinstallprompt', function (e) {
                e.preventDefault();
                deferredPrompt = e;

                // Mostrar banner solo si el usuario no tomó una decisión reciente
                if (shouldShow()) {
                    showBanner();
                }
            });

            // Cuando el usuario instala la PWA por otro medio
            window.addEventListener('appinstalled', function () {
                saveDecision('accepted');
                hideBanner();
                deferredPrompt = null;
            });

            // Click en "Instalar"
            btnInstall.addEventListener('click', async function () {
                if (!deferredPrompt) return;
                hideBanner();
                deferredPrompt.prompt();
                const { outcome } = await deferredPrompt.userChoice;
                if (outcome === 'accepted') {
                    saveDecision('accepted');
                } else {
                    saveDecision('dismissed');
                }
                deferredPrompt = null;
            });

            // Click en "Cerrar / Ahora no"
            btnDismiss.addEventListener('click', function () {
                saveDecision('dismissed');
                hideBanner();
            });
        })();
    </script>

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
