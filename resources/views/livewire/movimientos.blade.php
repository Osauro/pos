<div>
    <!-- Header -->
                    <div class="module-sticky-header">
                        <div class="d-flex justify-content-between align-items-center gap-2">
                            <h5 class="mb-0 fw-bold">Movimientos</h5>
                            <div class="d-flex align-items-center gap-2">
                                @if($mostrarFiltro)
                                    @if($turno_seleccionado)
                                        @php
                                            $turnoFiltro = \App\Models\Turno::find($turno_seleccionado);
                                        @endphp
                                        @if($turnoFiltro)
                                            <button class="btn btn-danger" wire:click="limpiarFiltroFechas" title="Limpiar filtro">
                                                Turno: {{ \Carbon\Carbon::parse($turnoFiltro->fecha_inicio)->format('d/m') }} - {{ \Carbon\Carbon::parse($turnoFiltro->fecha_fin)->format('d/m') }}
                                            </button>
                                        @endif
                                    @endif
                                    <button class="btn btn-outline-secondary" wire:click="abrirModalFiltro" title="Filtrar por fecha">
                                        <i class="fa-solid fa-calendar-days"></i>
                                    </button>
                                @endif
                                @if(auth()->user()->tipo === 'admin')
                                    <button class="btn btn-primary" wire:click="create">
                                        <i class="fa-solid fa-plus"></i>
                                    </button>
                                @endif
                            </div>
                        </div>
    </div>

    <div class="module-scroll-area p-2 p-md-3">
        <!-- Resumen de saldos - Oculto en móvil -->
                        <div class="row mb-3 d-none d-md-flex">
                            <div class="col-md-4 mb-2">
                                <div class="card shadow-sm border-0"
                                    style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%);">
                                    <div class="card-body py-3 text-white">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <p class="mb-1 opacity-75" style="font-size: 0.85rem;">Total Ingresos
                                                </p>
                                                <h3 class="mb-0 fw-bold">Bs. {{ number_format($totalIngresos, 2) }}</h3>
                                            </div>
                                            <div class="bg-white bg-opacity-25 rounded-circle p-3">
                                                <i class="fa-solid fa-arrow-trend-up fa-2x"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-2">
                                <div class="card shadow-sm border-0"
                                    style="background: linear-gradient(135deg, #dc3545 0%, #fd7e14 100%);">
                                    <div class="card-body py-3 text-white">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <p class="mb-1 opacity-75" style="font-size: 0.85rem;">Total Egresos</p>
                                                <h3 class="mb-0 fw-bold">Bs. {{ number_format($totalEgresos, 2) }}</h3>
                                            </div>
                                            <div class="bg-white bg-opacity-25 rounded-circle p-3">
                                                <i class="fa-solid fa-arrow-trend-down fa-2x"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-2">
                                <div class="card shadow-sm border-0"
                                    style="background: linear-gradient(135deg, #0d6efd 0%, #6610f2 100%);">
                                    <div class="card-body py-3 text-white">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <p class="mb-1 opacity-75" style="font-size: 0.85rem;">Saldo Actual</p>
                                                <h3 class="mb-0 fw-bold">Bs. {{ number_format($saldoActual, 2) }}</h3>
                                            </div>
                                            <div class="bg-white bg-opacity-25 rounded-circle p-3">
                                                <i class="fa-solid fa-wallet fa-2x"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tabla de movimientos -->
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th class="text-center" style="width: 100px;">Fecha</th>
                                        <th>Detalle</th>
                                        <th class="text-end" style="width: 150px;">Monto</th>
                                        <th class="text-end" style="width: 150px;">Saldo</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($movimientos as $movimiento)
                                        <tr>
                                            <td class="text-center text-truncate">
                                                <div class="fw-semibold">{{ $movimiento->created_at->format('d/m/Y') }}
                                                </div>
                                                <small
                                                    class="text-muted">{{ $movimiento->created_at->format('H:i') }}</small>
                                            </td>
                                            <td class="text-truncate">
                                                <small class="text-muted d-block">{{ $movimiento->turno->encargado->nombre ?? 'N/A' }}</small>
                                                <span>{{ $movimiento->detalle }}</span>
                                            </td>
                                            <td class="text-end text-truncate">
                                                @if ($movimiento->ingreso > 0)
                                                    <span class="text-success fw-semibold">
                                                        + Bs. {{ number_format($movimiento->ingreso, 2) }}
                                                    </span>
                                                @else
                                                    <span class="text-danger fw-semibold">
                                                        - Bs. {{ number_format($movimiento->egreso, 2) }}
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="text-end text-truncate">
                                                <span class="fw-bold">Bs.
                                                    {{ number_format($movimiento->saldo, 2) }}</span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-5">
                                                <i
                                                    class="fa-solid fa-file-invoice-dollar fa-3x text-muted mb-3 d-block"></i>
                                                <p class="text-muted mb-0">No hay movimientos registrados</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
    </div>

    @include('partials.paginate-bar', ['results' => $movimientos, 'storageKey' => 'movimientos'])

    <!-- Modal para Registrar Movimiento -->
    @if ($mostrarModal)
        <div class="modal fade show d-block" tabindex="-1" role="dialog" aria-labelledby="modalcrud"
            style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Registrar Movimiento</h5>
                        <button type="button" class="btn-close" wire:click="closeModal"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="save">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Tipo de Movimiento</label>
                                <div class="d-flex gap-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" wire:model="tipo" value="ingreso"
                                            id="tipoIngreso">
                                        <label class="form-check-label" for="tipoIngreso">
                                            <i class="fa-solid fa-arrow-up text-success"></i> Ingreso
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" wire:model="tipo" value="egreso"
                                            id="tipoEgreso">
                                        <label class="form-check-label" for="tipoEgreso">
                                            <i class="fa-solid fa-arrow-down text-danger"></i> Egreso
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Detalle</label>
                                <input type="text" class="form-control @error('detalle') is-invalid @enderror"
                                    wire:model="detalle" placeholder="Descripción del movimiento">
                                @error('detalle')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Monto (Bs.)</label>
                                <input type="number" step="0.01"
                                    class="form-control @error('monto') is-invalid @enderror" wire:model="monto"
                                    placeholder="0.00">
                                @error('monto')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="d-flex gap-2 justify-content-end">
                                <button type="button" class="btn btn-secondary"
                                    wire:click="closeModal">Cancelar</button>
                                <button type="submit" class="btn btn-primary">Guardar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal de Filtro de Fechas -->
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
                <div id="datepicker-movimientos" class="d-flex justify-content-center px-2 pb-3"></div>
            </div>
        </div>
    @endif

    @script
    <script>
        let flatpickrMovInstance = null;

        function cargarFlatpickrMov(callback) {
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
            cargarFlatpickrMov(() => initFlatpickrMov(data));
        });

        function initFlatpickrMov({ fechasValidas, rangoTurno, todosTurnos, fechaSeleccionada, turnoSeleccionadoId }) {
            if (flatpickrMovInstance) {
                flatpickrMovInstance.destroy();
                flatpickrMovInstance = null;
            }

            const element = document.getElementById('datepicker-movimientos');
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

            flatpickrMovInstance = flatpickr('#datepicker-movimientos', {
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
        #datepicker-movimientos {
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
</div>

