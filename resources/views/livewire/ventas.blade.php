<div>
    <!-- Header -->
                    <div class="module-sticky-header">
                        <div class="d-flex justify-content-between align-items-center gap-2">
                            <h5 class="mb-0 fw-bold">Ventas</h5>
                            <div class="d-flex align-items-center gap-2">
                                @if($mostrarFiltro)
                                    @if($turno_seleccionado)
                                        @php
                                            $turnoHeader = \App\Models\Turno::find($turno_seleccionado);
                                        @endphp
                                        @if($turnoHeader)
                                            <button class="btn btn-danger" wire:click="limpiarFiltroFechas" title="Limpiar filtro">
                                                Turno: {{ \Carbon\Carbon::parse($turnoHeader->fecha_inicio)->format('d/m') }} - {{ \Carbon\Carbon::parse($turnoHeader->fecha_fin)->format('d/m') }}
                                            </button>
                                        @endif
                                    @endif
                                    <button class="btn btn-outline-secondary" wire:click="abrirModalFiltro" title="Filtrar por fecha">
                                        <i class="fa-solid fa-calendar-days"></i>
                                    </button>
                                @endif
                                @if($puedeCrearVenta)
                                    <button class="btn btn-primary" wire:click="crearVenta">
                                        <i class="fa-solid fa-plus"></i>
                                    </button>
                                @endif
                            </div>
                        </div>
    </div>

    <div class="module-scroll-area">
        <div class="row g-2 p-2">
                            @forelse($ventas as $venta)
                                <div class="col-md-4 col-12">
                                    <div class="card mb-0 shadow-sm {{ $venta->estado === 'Cancelado' ? 'opacity-50' : '' }}">
                                        <div class="card-body compra-card-body">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div class="flex-grow-1">
                                                    <!-- Header -->
                                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                                        <h4 class="mb-0 fw-bold">
                                                            Venta #{{ $venta->numero_venta }}
                                                            @if(isset($venta->estado))
                                                                @if($venta->estado === 'Cancelado')
                                                                    <span class="badge bg-danger ms-2">Cancelada</span>
                                                                @elseif($venta->estado === 'Completo')
                                                                    <span class="badge bg-success ms-2">Completa</span>
                                                                @endif
                                                            @endif
                                                        </h4>
                                                        <div class="d-flex gap-1">
                                                            <button class="btn btn-sm btn-info"
                                                                wire:click="verDetalles({{ $venta->id }})"
                                                                title="Ver detalles">
                                                                <i class="fa-solid fa-eye"></i>
                                                            </button>
                                                            @if(!isset($venta->estado) || $venta->estado !== 'Cancelado')
                                                                <button class="btn btn-sm btn-success"
                                                                    wire:click="reimprimirVenta({{ $venta->id }})"
                                                                    title="Reimprimir ticket y comanda">
                                                                    <i class="fa-solid fa-print"></i>
                                                                </button>
                                                            @endif
                                                        </div>
                                                    </div>

                                                    <!-- Avatar Group de productos -->
                                                    <div class="avatar-group mb-1">
                                                        @foreach ($venta->ventaItems as $item)
                                                            <div class="avatar"
                                                                title="{{ $item->producto->nombre ?? 'Producto' }}">
                                                                <img src="{{ $item->producto->photo_url }}"
                                                                    alt="{{ $item->producto->nombre }}">
                                                                <span
                                                                    class="quantity-badge">{{ $item->cantidad }}</span>
                                                            </div>
                                                        @endforeach
                                                    </div>

                                                    <!-- Badges de totales -->
                                                    <div class="d-flex gap-2 flex-wrap mb-1">
                                                        @php
                                                            $total = $venta->efectivo + $venta->online + $venta->credito;
                                                        @endphp
                                                        <span class="badge bg-primary d-none d-md-inline">Total:
                                                            Bs. {{ number_format($total, 2) }}</span>
                                                        @if ($venta->efectivo > 0)
                                                            <span class="badge bg-success">
                                                                Bs. {{ number_format($venta->efectivo, 2) }}</span>
                                                        @endif
                                                        @if ($venta->online > 0)
                                                            <span class="badge bg-info">
                                                                Bs. {{ number_format($venta->online, 2) }}</span>
                                                        @endif
                                                        @if ($venta->credito > 0)
                                                            <span class="badge bg-danger">
                                                                Bs. {{ number_format($venta->credito, 2) }}</span>
                                                        @endif
                                                    </div>

                                                    <!-- Footer info -->
                                                    <div
                                                        class="d-flex justify-content-between align-items-center text-muted">
                                                        <small><i
                                                                class="fa-solid fa-user me-1"></i>{{ $venta->user->nombre ?? '—' }}</small>
                                                        <small><i
                                                                class="fa-solid fa-calendar me-1"></i>{{ $venta->created_at->format('d/m/Y H:i') }}</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12">
                                    <div class="text-center py-5 empty-state">
                                        <i class="fa-solid fa-shopping-cart fa-5x mb-3 text-muted"></i>
                                        <p class="h5 text-muted mb-0">No se encontraron ventas</p>
                                    </div>
                                </div>
                            @endforelse
        </div>
    </div>

    @include('partials.paginate-bar', ['results' => $ventas, 'storageKey' => 'ventas'])

    <!-- Modal de Detalles de Venta -->
    @if ($mostrarModal && $ventaSeleccionada)
        <!-- Backdrop del Modal -->
        <div class="modal-backdrop fade show" style="z-index: 1040;"></div>

        <!-- Modal -->
        <div class="modal fade show d-block" tabindex="-1" role="dialog" aria-modal="true"
            style="z-index: 1050; overflow-y: auto;">
            <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                <div class="modal-content shadow-lg border-0">
                    <div class="modal-header text-white" style="background-color: var(--theme-default, #7366ff);">
                        <h5 class="modal-title mb-0">
                            <i class="fa-solid fa-shopping-cart me-2"></i>Venta #{{ $ventaSeleccionada->numero_venta }}
                        </h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="cerrarModal"
                            aria-label="Cerrar"></button>
                    </div>

                    <div class="modal-body p-0">
                        <!-- Tabla de productos -->
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="align-middle">Producto</th>
                                        <th class="text-end align-middle" style="width:60px;">Cant.</th>
                                        <th class="text-end align-middle" style="width:100px;">Precio</th>
                                        <th class="text-end align-middle" style="width:110px;">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($ventaSeleccionada->ventaItems as $item)
                                        <tr>
                                            <td class="align-middle">
                                                <strong>{{ $item->producto->nombre ?? 'Producto' }}</strong>
                                                @if(is_array($item->detalle))
                                                    @php
                                                        $partes = [];
                                                        if(($item->detalle['arroz'] ?? 0) > 0) $partes[] = $item->detalle['arroz'].'A';
                                                        if(($item->detalle['fideo'] ?? 0) > 0) $partes[] = $item->detalle['fideo'].'F';
                                                        if(($item->detalle['mixto'] ?? 0) > 0) $partes[] = $item->detalle['mixto'].'M';
                                                    @endphp
                                                    @if(count($partes))
                                                        <small class="text-muted ms-1">({{ implode(', ', $partes) }})</small>
                                                    @endif
                                                @endif
                                            </td>
                                            <td class="text-end align-middle">{{ $item->cantidad }}</td>
                                            <td class="text-end align-middle">Bs. {{ number_format($item->precio, 2) }}</td>
                                            <td class="text-end align-middle"><strong>Bs. {{ number_format($item->subtotal, 2) }}</strong></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <td colspan="{{ 3 }}" class="text-end fw-bold">TOTAL</td>
                                        <td class="text-end fw-bold fs-6">Bs. {{ number_format($ventaSeleccionada->ventaItems->sum('subtotal'), 2) }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <div class="modal-footer bg-light py-2">
                        <div class="d-flex align-items-center justify-content-between w-100 gap-3">
                            <div class="d-flex align-items-center gap-3 text-muted small">
                                <span><i class="fa-solid fa-user me-1"></i><strong>{{ $ventaSeleccionada->user->nombre ?? '—' }}</strong></span>
                                <span><i class="fa-solid fa-calendar me-1"></i>{{ $ventaSeleccionada->fecha_hora ? $ventaSeleccionada->fecha_hora->format('d/m/Y H:i') : ($ventaSeleccionada->created_at ? $ventaSeleccionada->created_at->format('d/m/Y H:i') : '—') }}</span>
                            </div>
                            <div class="d-flex gap-1">
                                @if ($ventaSeleccionada->estado === 'Completo')
                                    <button class="btn btn-sm btn-success"
                                        wire:click="reimprimirVenta({{ $ventaSeleccionada->id }})"
                                        title="Reimprimir ticket y comanda">
                                        <i class="fa-solid fa-print me-1"></i>Reimprimir
                                    </button>
                                @endif
                                @if ($ventaSeleccionada->estado === 'Completo' && $puedeEliminar)
                                    <button class="btn btn-sm btn-danger"
                                        wire:click="$dispatch('confirm-delete', { id: {{ $ventaSeleccionada->id }}, message: '¿Está seguro de eliminar la venta #{{ $ventaSeleccionada->numero_venta }}?' })"
                                        title="Eliminar">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal de Resumen de Eliminación -->
    {{-- Modal deshabilitado - no se usa stock --}}
    @if (false && $mostrarResumenEliminacion && !empty($resumenEliminacion))
        <!-- Backdrop del Modal -->
        <div class="modal-backdrop fade show" style="z-index: 1040;"></div>

        <!-- Modal -->
        <div class="modal fade show d-block" tabindex="-1" role="dialog" aria-modal="true"
            style="z-index: 1050; overflow-y: auto;">
            <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                <div class="modal-content shadow-lg border-0">
                    <div class="modal-header text-white" style="background-color: var(--theme-default, #7366ff);">
                        <h5 class="modal-title mb-0">
                            <i class="fa-solid fa-check-circle me-2"></i>
                            Venta Eliminada #{{ $resumenEliminacion['venta_id'] }}
                        </h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="cerrarResumen"
                            aria-label="Cerrar"></button>
                    </div>

                    <div class="modal-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="align-middle">Producto</th>
                                        <th class="text-end align-middle">Anterior</th>
                                        <th class="text-end align-middle">Cantidad</th>
                                        <th class="text-end align-middle">Nuevo</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($resumenEliminacion['productos'] as $producto)
                                        <tr>
                                            <td class="align-middle text-truncate">
                                                <strong>{{ $producto['nombre'] }}</strong>
                                            </td>
                                            <td class="text-end align-middle text-truncate">
                                                <span class="badge bg-warning text-dark">
                                                    {{ $producto['stock_anterior_formateado'] }}
                                                </span>
                                            </td>
                                            <td class="text-end align-middle text-truncate">
                                                <span class="badge bg-info text-dark">
                                                    {{ $producto['cantidad_formateada'] }}
                                                </span>
                                            </td>
                                            <td class="text-end align-middle text-truncate">
                                                <span class="badge bg-success">
                                                    {{ $producto['stock_nuevo_formateado'] }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="modal-footer bg-light">
                        <div class="row g-2 justify-content-center w-100">
                            <div class="col-6 col-md-4">
                                <div class="rounded px-3 py-2 text-center h-100 d-flex flex-column justify-content-center" style="background-color: #f0f0f0;">
                                    <small class="text-dark d-block">Retirado de Caja</small>
                                    <span class="fw-bold fs-5 text-danger">Bs. {{ number_format($resumenEliminacion['devuelto_caja'] ?? 0, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @script
        <script>
            // Gestionar el estado del body cuando hay modales abiertos
            $wire.on('$refresh', () => {
                if ($wire.mostrarModal) {
                    document.body.classList.add('modal-open');
                    document.body.style.overflow = 'hidden';
                    document.body.style.paddingRight = '0px';
                } else {
                    document.body.classList.remove('modal-open');
                    document.body.style.overflow = '';
                    document.body.style.paddingRight = '';
                }
            });

            // Cerrar modal al hacer clic fuera del contenido
            document.addEventListener('click', function(e) {
                // Detectar clic en el backdrop
                if (e.target.classList.contains('modal-backdrop')) {
                    if ($wire.mostrarModal) {
                        $wire.call('cerrarModal');
                    }
                }

                // Detectar clic en el área del modal pero fuera del modal-content
                if (e.target.classList.contains('modal') && e.target.classList.contains('show')) {
                    if ($wire.mostrarModal) {
                        $wire.call('cerrarModal');
                    }
                }
            });

            $wire.on('alert', (event) => {
                // En Livewire 3, el evento llega como array
                const data = event[0] || event;
                Swal.fire({
                    title: data.type === 'success' ? '¡Éxito!' : 'Error',
                    text: data.message,
                    icon: data.type,
                    confirmButtonColor: data.type === 'success' ? '#28a745' : '#d33',
                    confirmButtonText: 'Aceptar'
                });
            });

            $wire.on('confirm-delete', (event) => {
                const data = event[0] || event;
                Swal.fire({
                    title: '¿Está seguro?',
                    text: data.message,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $wire.call('delete', data.id);
                    }
                });
            });

            // === IMPRESIÓN VÍA PRINT-AGENT (print:// protocol) ===
            function launchProtocol(url, delay = 0) {
                const a = document.createElement('a');
                a.href = url;
                a.style.display = 'none';
                document.body.appendChild(a);
                setTimeout(() => { a.click(); document.body.removeChild(a); }, delay);
            }

            $wire.on('imprimir-venta', (data) => {
                const d          = data[0] || data;
                const ticketUrl  = d.ticketUrl  ?? null;
                const comandaUrl = d.comandaUrl ?? null;
                if (!d.ventaId) return;

                // Solo Android usa fallback HTML/PDF.
                const isAndroid = /Android/i.test(navigator.userAgent);

                if (!isAndroid && ticketUrl) {
                    launchProtocol(ticketUrl);
                    if (comandaUrl) launchProtocol(comandaUrl, 600);
                } else if (isAndroid) {
                    window.open(`/ticket/cliente/${d.ventaId}?nocomanda=1`, '_blank');
                    if (comandaUrl) setTimeout(() => window.open(`/ticket/comanda/${d.ventaId}`, '_blank'), 10000);
                }
            });
        </script>
    @endscript

    <!-- Modal de Filtro de Fechas - Overlay Completo -->
    @if ($mostrarModalFiltro)
        <div class="position-fixed top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center"
             style="background-color: rgba(0,0,0,0.8); z-index: 9999; padding: 10px;"
             wire:click="cerrarModalFiltro">
            <div class="bg-white rounded shadow-lg"
                 style="max-width: 95vw; max-height: 95vh; overflow: hidden;"
                 onclick="event.stopPropagation();">
                <div class="d-flex justify-content-between align-items-center px-3 pt-3 pb-2">
                    <h6 class="mb-0 fw-bold text-muted">
                        <i class="fa-solid fa-calendar-days me-2"></i>Selecciona un turno
                    </h6>
                    <button type="button" class="btn-close" wire:click="cerrarModalFiltro"></button>
                </div>
                <div id="datepicker-ventas" class="d-flex justify-content-center px-2 pb-3"></div>
            </div>
        </div>
    @endif

    @script
    <script>
        let flatpickrInstance = null;

        function cargarFlatpickr(callback) {
            if (typeof flatpickr !== 'undefined' && typeof flatpickr.l10ns !== 'undefined' && flatpickr.l10ns.es) {
                callback();
                return;
            }
            if (typeof flatpickr === 'undefined') {
                const link = document.createElement('link');
                link.rel = 'stylesheet';
                link.href = 'https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.css';
                document.head.appendChild(link);

                const script = document.createElement('script');
                script.src = 'https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.js';
                script.onload = function() {
                    const scriptEs = document.createElement('script');
                    scriptEs.src = 'https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/l10n/es.js';
                    scriptEs.onload = callback;
                    document.body.appendChild(scriptEs);
                };
                document.body.appendChild(script);
            } else {
                const scriptEs = document.createElement('script');
                scriptEs.src = 'https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/l10n/es.js';
                scriptEs.onload = callback;
                document.body.appendChild(scriptEs);
            }
        }

        $wire.on('calendarioAbierto', (data) => {
            cargarFlatpickr(() => initFlatpickr(data));
        });

        function initFlatpickr({ fechasValidas, rangoTurno, todosTurnos, fechaSeleccionada, turnoSeleccionadoId }) {
            if (flatpickrInstance) {
                flatpickrInstance.destroy();
                flatpickrInstance = null;
            }

            const element = document.getElementById('datepicker-ventas');
            if (!element) return;
            element.innerHTML = '';

            let fechaInicial;
            if (fechaSeleccionada) {
                const p = fechaSeleccionada.substring(0, 10).split('-');
                fechaInicial = new Date(p[0], p[1] - 1, p[2]);
            } else {
                fechaInicial = new Date();
            }

            const parseLocalDate = (str) => {
                const d = str.substring(0, 10).split('-');
                return new Date(d[0], d[1] - 1, d[2]).getTime();
            };

            flatpickrInstance = flatpickr('#datepicker-ventas', {
                locale: 'es',
                dateFormat: 'd/m/Y',
                enable: fechasValidas.length > 0 ? fechasValidas.map(f => {
                    const parts = f.substring(0, 10).split('-');
                    return new Date(parts[0], parts[1] - 1, parts[2]);
                }) : [],
                inline: true,
                monthSelectorType: 'static',
                showMonths: 1,
                defaultDate: fechaInicial,
                onDayCreate: function(dObj, dStr, fp, dayElem) {
                    const dayDate = dayElem.dateObj;
                    const dayTime = new Date(dayDate.getFullYear(), dayDate.getMonth(), dayDate.getDate()).getTime();

                    if (rangoTurno) {
                        const inicioTime = parseLocalDate(rangoTurno.inicio);
                        const finTime = parseLocalDate(rangoTurno.fin);
                        if (dayTime >= inicioTime && dayTime <= finTime) {
                            dayElem.classList.add('turno-seleccionado');
                            return;
                        }
                    }

                    for (let turno of todosTurnos) {
                        if (turnoSeleccionadoId && turno.id === turnoSeleccionadoId) continue;
                        const inicioTime = parseLocalDate(turno.inicio);
                        const finTime = parseLocalDate(turno.fin);
                        if (dayTime >= inicioTime && dayTime <= finTime) {
                            dayElem.classList.add('turno-disponible');
                            break;
                        }
                    }
                },
                onChange: function(selectedDates) {
                    if (selectedDates.length > 0) {
                        const fecha = selectedDates[0];
                        const fechaFormateada = fecha.getFullYear() + '-' +
                            String(fecha.getMonth() + 1).padStart(2, '0') + '-' +
                            String(fecha.getDate()).padStart(2, '0');
                        $wire.call('seleccionarFecha', fechaFormateada);
                    }
                }
            });
        }
    </script>
    @endscript

    <style>
        /* Estilos Flatpickr - solo visuales, sin alterar layout interno */
        #datepicker-ventas {
            display: flex;
            justify-content: center;
            overflow-x: auto;
        }

        .flatpickr-calendar {
            box-shadow: none !important;
            border: none !important;
        }

        .flatpickr-months {
            padding: 5px 10px !important;
        }

        .flatpickr-current-month {
            font-size: 18px !important;
            font-weight: 600 !important;
        }

        .flatpickr-weekday {
            font-size: 13px !important;
            font-weight: bold !important;
            color: #666 !important;
        }

        .flatpickr-day {
            border-radius: 8px !important;
            font-size: 14px !important;
            color: #333 !important;
        }

        .flatpickr-day.disabled {
            color: #e57373 !important;
            cursor: not-allowed !important;
            background: #ffebee !important;
            border: 2px solid #ef5350 !important;
        }

        .flatpickr-day.disabled:hover {
            background: #ffebee !important;
            color: #e57373 !important;
            border-color: #ef5350 !important;
        }

        .flatpickr-day:not(.disabled):hover {
            background: #e3f2fd !important;
            border-color: #2196f3 !important;
        }

        .flatpickr-day.today:not(.selected) {
            background: #fff3e0 !important;
            color: #f57c00 !important;
            border-color: #ff9800 !important;
            font-weight: 600 !important;
        }

        .flatpickr-day.turno-seleccionado:not(.disabled) {
            background: #4caf50 !important;
            border: 2px solid #4caf50 !important;
            font-weight: 600 !important;
            color: white !important;
        }

        .flatpickr-day.turno-seleccionado:not(.disabled):hover {
            background: #45a049 !important;
            border-color: #45a049 !important;
            color: white !important;
        }

        .flatpickr-day.turno-disponible:not(.disabled) {
            background: white !important;
            border: 2px solid #4caf50 !important;
            font-weight: 600 !important;
            color: #2e7d32 !important;
        }

        .flatpickr-day.turno-disponible:not(.disabled):hover {
            background: #e8f5e9 !important;
            border-color: #4caf50 !important;
            color: #1b5e20 !important;
        }
    </style>

    <!-- Overlay de Pago de Crédito - Paso 1: Añadir Fondos -->
    {{-- Modal de Pago de Crédito - DESHABILITADO --}}
    @if (false && $mostrarModalPago && $ventaAPagar)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(255,255,255,0.95); overflow-y: auto;"
            x-data="{
                efectivo: {{ $montoPagoEfectivo ?? 0 }},
                online: {{ $montoPagoOnline ?? 0 }},
                creditoTotal: {{ $ventaAPagar->credito }},
                get totalPago() {
                    return parseFloat(this.efectivo || 0) + parseFloat(this.online || 0);
                },
                get creditoRestante() {
                    return Math.max(0, this.creditoTotal - this.totalPago);
                },
                finalizarPago() {
                    if ({{ $procesandoPago ? 'true' : 'false' }} || this.totalPago <= 0 || this.totalPago > this.creditoTotal) {
                        return;
                    }
                    $wire.set('montoPagoEfectivo', this.efectivo);
                    $wire.set('montoPagoOnline', this.online);
                    $wire.pagarCredito();
                }
            }"
            @keydown.enter="finalizarPago()">
            <div class="modal-dialog modal-dialog-centered" style="max-width: 600px;">
                <div class="modal-content shadow-lg">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title">
                            <i class="fa-solid fa-money-bill me-2"></i>
                            Pagar Crédito
                        </h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="cerrarModalPago" {{ $procesandoPago ? 'disabled' : '' }}></button>
                    </div>
                    <div class="modal-body">
                        <!-- Información de la Venta -->
                        <div class="row g-2 mb-4">
                            <div class="col-6">
                                <div class="p-2 bg-light rounded text-center">
                                    <small class="text-muted d-block">Venta:</small>
                                    <strong class="d-block text-dark">#{{ $ventaAPagar->numero_venta }}</strong>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-2 bg-light rounded text-center">
                                    <small class="text-muted d-block">Cliente:</small>
                                    <strong class="d-block text-truncate text-dark px-2" title="{{ $ventaAPagar->cliente->nombre ?? 'Sin cliente' }}">{{ $ventaAPagar->cliente->nombre ?? 'Sin cliente' }}</strong>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <!-- Total Crédito -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Total Crédito</label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text">Bs.</span>
                                    <input type="number"
                                        class="form-control bg-danger bg-opacity-10 text-danger fw-bold text-end"
                                        :value="creditoTotal"
                                        disabled>
                                </div>
                            </div>

                            <!-- Efectivo -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Efectivo</label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text">Bs.</span>
                                    <input type="number"
                                        id="montoPagoEfectivo"
                                        class="form-control text-end"
                                        x-model.number="efectivo"
                                        step="0.01"
                                        min="0"
                                        :max="creditoTotal"
                                        placeholder="0.00"
                                        {{ $procesandoPago ? 'disabled' : '' }}>
                                </div>
                            </div>

                            <!-- Online -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Online</label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text">Bs.</span>
                                    <input type="number"
                                        id="montoPagoOnline"
                                        class="form-control text-end"
                                        x-model.number="online"
                                        step="0.01"
                                        min="0"
                                        :max="creditoTotal"
                                        placeholder="0.00"
                                        {{ $procesandoPago ? 'disabled' : '' }}>
                                </div>
                            </div>

                            <!-- Crédito Restante -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Crédito Restante</label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text">Bs.</span>
                                    <input type="number"
                                        class="form-control fw-bold text-end"
                                        :class="creditoRestante > 0 ? 'bg-warning bg-opacity-10 text-warning' : 'bg-success bg-opacity-10 text-success'"
                                        :value="creditoRestante.toFixed(2)"
                                        disabled>
                                </div>
                            </div>
                        </div>

                        <!-- Resumen en barra horizontal -->
                        <div class="row g-2 mb-3">
                            <div class="col-4">
                                <div class="p-2 bg-light rounded text-center">
                                    <small class="text-muted d-block">Total a Pagar:</small>
                                    <strong class="d-block" :class="totalPago > 0 ? 'text-success' : 'text-muted'">
                                        Bs. <span x-text="totalPago.toFixed(2)">0.00</span>
                                    </strong>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-2 bg-light rounded text-center">
                                    <small class="text-muted d-block">Efectivo:</small>
                                    <strong class="d-block text-primary">Bs. <span x-text="(efectivo || 0).toFixed(2)">0.00</span></strong>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-2 bg-light rounded text-center">
                                    <small class="text-muted d-block">Online:</small>
                                    <strong class="d-block text-info">Bs. <span x-text="(online || 0).toFixed(2)">0.00</span></strong>
                                </div>
                            </div>
                        </div>

                        <div x-show="totalPago > creditoTotal" class="alert alert-danger mb-0">
                            <i class="fa-solid fa-exclamation-triangle me-1"></i>
                            El monto total excede la deuda pendiente
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button"
                            class="btn btn-secondary"
                            wire:click="cerrarModalPago"
                            {{ $procesandoPago ? 'disabled' : '' }}>
                            <i class="fa-solid fa-times me-1"></i>
                            Cancelar
                        </button>
                        <button type="button"
                            class="btn btn-success"
                            @click="finalizarPago()"
                            :disabled="{{ $procesandoPago ? 'true' : 'false' }} || totalPago <= 0 || totalPago > creditoTotal">
                            @if ($procesandoPago)
                                <span class="spinner-border spinner-border-sm me-1"></span>
                                Procesando...
                            @else
                                <i class="fa-solid fa-check me-1"></i>
                                Finalizar Pago <span class="badge bg-white text-success ms-1">Enter</span>
                            @endif
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal de Procesando (Spinner Completo) - DESHABILITADO --}}
    @if (false && $procesandoPago && $mostrarModalPago)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.7); z-index: 1060;">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content bg-transparent border-0 shadow-lg">
                    <div class="modal-body text-center py-5">
                        <div class="spinner-border text-primary mb-3" role="status" style="width: 4rem; height: 4rem;">
                            <span class="visually-hidden">Procesando...</span>
                        </div>
                        <h5 class="text-white mb-2">
                            <i class="fa-solid fa-clock me-2"></i>
                            Procesando pago
                        </h5>
                        <p class="text-white-50 mb-0">Por favor espere mientras se completa la transacción</p>
                        <div class="progress mt-3" style="height: 3px;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" role="progressbar" style="width: 100%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Script de modal de pago deshabilitado --}}
    {{-- @script
        <script>
            // Manejo de teclado para el modal de pago
            document.addEventListener('keydown', function(e) {
                // Solo si el modal de pago está abierto y no está procesando
                if (!$wire.mostrarModalPago || $wire.procesandoPago) return;

                // Enter para procesar pago
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const efectivo = parseFloat($wire.montoPagoEfectivo || 0);
                    const online = parseFloat($wire.montoPagoOnline || 0);
                    const totalPago = efectivo + online;
                    const creditoPendiente = parseFloat($wire.ventaAPagar.credito);

                    // Validar que el monto sea válido
                    if (totalPago > 0 && totalPago <= creditoPendiente) {
                        $wire.pagarCredito();
                    }
                }

                // Escape para cerrar
                if (e.key === 'Escape') {
                    e.preventDefault();
                    $wire.cerrarModalPago();
                }
            });

            // Focus inicial al abrir modal
            Livewire.hook('morph.updated', ({ el, component }) => {
                if ($wire.mostrarModalPago && !$wire.procesandoPago) {
                    setTimeout(() => {
                        const input = document.querySelector('#montoPagoEfectivo');
                        if (input) {
                            input.focus();
                            input.select();
                        }
                    }, 100);
                }
            });
        </script>
    @endscript --}}
    {{-- Componente anidado de Kardex Modal - DESHABILITADO --}}
    {{-- <livewire:kardex-modal /> --}}
</div>
