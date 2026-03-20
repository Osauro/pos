<!DOCTYPE html>
<html lang="es">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description" content="TPV — Sistema de Punto de Venta" />
    <meta name="robots" content="noindex, nofollow" />
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo $__env->yieldContent('title', config('app.name', 'TPV')); ?></title>

    <!-- Favicon -->
    <link rel="icon" href="<?php echo e(asset('assets/images/favicon.png')); ?>" type="image/x-icon" />
    <link rel="shortcut icon" href="<?php echo e(asset('assets/images/favicon.png')); ?>" type="image/x-icon" />

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:opsz,wght@6..12,200;6..12,300;6..12,400;6..12,500;6..12,600;6..12,700;6..12,800;6..12,900;6..12,1000&display=swap" rel="stylesheet" />

    <!-- Flag icon css -->
    <link rel="stylesheet" href="<?php echo e(asset('assets/css/vendors/flag-icon.css')); ?>" />
    <!-- iconly-icon-->
    <link rel="stylesheet" href="<?php echo e(asset('assets/css/iconly-icon.css')); ?>" />
    <link rel="stylesheet" href="<?php echo e(asset('assets/css/bulk-style.css')); ?>" />
    <!-- iconly-icon-->
    <link rel="stylesheet" href="<?php echo e(asset('assets/css/themify.css')); ?>" />
    <!--fontawesome-->
    <link rel="stylesheet" href="<?php echo e(asset('assets/css/fontawesome-min.css')); ?>" />
    <!-- Weather Icon css-->
    <link rel="stylesheet" type="text/css" href="<?php echo e(asset('assets/css/vendors/weather-icons/weather-icons.min.css')); ?>" />
    <link rel="stylesheet" type="text/css" href="<?php echo e(asset('assets/css/vendors/scrollbar.css')); ?>" />
    <link rel="stylesheet" type="text/css" href="<?php echo e(asset('assets/css/vendors/slick.css')); ?>" />
    <link rel="stylesheet" type="text/css" href="<?php echo e(asset('assets/css/vendors/slick-theme.css')); ?>" />
    <!-- App css -->
    <link rel="stylesheet" href="<?php echo e(asset('assets/css/style.css')); ?>" />

    <?php
        $themeNumber = 5;
        $themeColor = '#884A39';
    ?>

    <link id="color" rel="stylesheet" href="<?php echo e(asset('assets/css/color-' . $themeNumber . '.css')); ?>?v=<?php echo e(time()); ?>" media="screen" />

    <style>
        :root {
            --theme-default: <?php echo e($themeColor); ?>;
            --primary-color: <?php echo e($themeColor); ?>;
            --livewire-progress-bar-color: <?php echo e($themeColor); ?> !important;
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
    <link rel="stylesheet" href="<?php echo e(asset('assets/css/custom.css')); ?>?v=<?php echo e(filemtime(public_path('assets/css/custom.css'))); ?>" />

    <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::styles(); ?>

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

        <?php echo $__env->make('layouts.theme.header', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <!-- Page Body Start-->
        <div class="page-body-wrapper">

            <!-- Page sidebar start-->
            <?php echo $__env->make('layouts.theme.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <!-- Page sidebar end-->

            <div class="page-body">
                <?php echo e($slot); ?>


                <!-- Espaciador para vista móvil (barra inferior fija) -->
                <div class="d-md-none" style="min-height: 70px;"></div>
            </div>

        </div>

    </div>

    <!-- jquery-->
    <script src="<?php echo e(asset('assets/js/vendors/jquery/jquery.min.js')); ?>"></script>
    <!-- bootstrap js-->
    <script src="<?php echo e(asset('assets/js/vendors/bootstrap/dist/js/bootstrap.bundle.min.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/js/vendors/bootstrap/dist/js/popper.min.js')); ?>"></script>
    <!--fontawesome-->
    <script src="<?php echo e(asset('assets/js/vendors/font-awesome/fontawesome-min.js')); ?>"></script>
    <!-- feather-->
    <script src="<?php echo e(asset('assets/js/vendors/feather-icon/feather.min.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/js/vendors/feather-icon/custom-script.js')); ?>"></script>
    <!-- sidebar -->
    <script src="<?php echo e(asset('assets/js/sidebar.js')); ?>"></script>
    <!-- height_equal-->
    <script src="<?php echo e(asset('assets/js/height-equal.js')); ?>"></script>
    <!-- config-->
    <script src="<?php echo e(asset('assets/js/config.js')); ?>"></script>
    <!-- apex-->
    <script src="<?php echo e(asset('assets/js/chart/apex-chart/apex-chart.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/js/chart/apex-chart/stock-prices.js')); ?>"></script>
    <!-- scrollbar-->
    <script src="<?php echo e(asset('assets/js/scrollbar/simplebar.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/js/scrollbar/custom.js')); ?>"></script>
    <!-- slick-->
    <script src="<?php echo e(asset('assets/js/slick/slick.min.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/js/slick/slick.js')); ?>"></script>
    <!-- theme_customizer-->
    <script src="<?php echo e(asset('assets/js/theme-customizer/customizer.js')); ?>"></script>
    <!-- tilt-->
    <script src="<?php echo e(asset('assets/js/animation/tilt/tilt.jquery.js')); ?>"></script>
    <!-- page_tilt-->
    <script src="<?php echo e(asset('assets/js/animation/tilt/tilt-custom.js')); ?>"></script>
    <!-- custom script -->
    <script src="<?php echo e(asset('assets/js/script.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/js/toasts-custom.js')); ?>"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Custom JS -->
    <script src="<?php echo e(asset('assets/js/custom.js')); ?>"></script>

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
            localStorage.setItem("color", 'color-5');
            localStorage.setItem("primary", '#884A39');
            localStorage.setItem("secondary", '#C38154');
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

    <?php echo $__env->yieldPushContent('scripts'); ?>

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

    <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::scripts(); ?>

</body>

</html>
<?php /**PATH C:\laragon\www\tpv\resources\views/layouts/theme/app.blade.php ENDPATH**/ ?>