<div class="container-fluid">

    {{-- ── Tarjetas resumen ─────────────────────────────────────────────── --}}
    <div class="row g-2 mb-3">

        <div class="col-6 col-sm-3">
            <div class="card o-hidden border-0 mb-0">
                <div class="card-body" style="border-left: 4px solid var(--theme-default); min-height: 90px;">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="mb-1 text-muted small">Ventas hoy</p>
                            <h4 class="mb-0 fw-bold" style="color: var(--theme-default);">{{ $ventasHoy }}</h4>
                            <small class="text-muted">transacciones</small>
                        </div>
                        <div class="rounded-circle p-3" style="background-color: rgba(var(--theme-default-rgb), 0.1);">
                            <i class="fa-solid fa-shopping-cart fa-2x" style="color: var(--theme-default);"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-sm-3">
            <div class="card o-hidden border-0 mb-0">
                <div class="card-body" style="border-left: 4px solid #28a745; min-height: 90px;">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="mb-1 text-muted small">Ingreso hoy</p>
                            <h4 class="mb-0 fw-bold" style="color:#28a745;">Bs. {{ number_format($ingresoHoy, 2) }}</h4>
                            <small class="text-muted">cobrado</small>
                        </div>
                        <div class="rounded-circle p-3" style="background-color: rgba(40,167,69,0.1);">
                            <i class="fa-solid fa-coins fa-2x" style="color:#28a745;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-sm-3">
            <div class="card o-hidden border-0 mb-0">
                <div class="card-body" style="border-left: 4px solid #17a2b8; min-height: 90px;">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="mb-1 text-muted small">Ventas mes</p>
                            <h4 class="mb-0 fw-bold" style="color:#17a2b8;">{{ $ventasMes }}</h4>
                            <small class="text-muted">{{ now()->isoFormat('MMMM') }}</small>
                        </div>
                        <div class="rounded-circle p-3" style="background-color: rgba(23,162,184,0.1);">
                            <i class="fa-solid fa-calendar-check fa-2x" style="color:#17a2b8;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-sm-3">
            <div class="card o-hidden border-0 mb-0">
                <div class="card-body" style="border-left: 4px solid #fd7e14; min-height: 90px;">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="mb-1 text-muted small">Ingreso mes</p>
                            <h4 class="mb-0 fw-bold" style="color:#fd7e14;">Bs. {{ number_format($ingresoMes, 2) }}</h4>
                            <small class="text-muted">acumulado</small>
                        </div>
                        <div class="rounded-circle p-3" style="background-color: rgba(253,126,20,0.1);">
                            <i class="fa-solid fa-chart-bar fa-2x" style="color:#fd7e14;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- ── Ventas de la semana + Productos vendidos hoy ─────────────────── --}}
    <div class="row g-2 mb-3">

        {{-- Gráfico semanal --}}
        <div class="col-lg-6 mb-3 mb-lg-0">
            <div class="card" style="min-height: 350px;">
                <div class="card-header pb-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-1">Ventas de la semana</h5>
                            <small class="text-muted">
                                {{ \Carbon\Carbon::parse($semanaFecha)->startOfWeek()->format('d/m/Y') }} -
                                {{ \Carbon\Carbon::parse($semanaFecha)->endOfWeek()->format('d/m/Y') }}
                            </small>
                        </div>
                        @if(! $this->esUser())
                        <div>
                            <input type="date" class="form-control form-control-sm" style="width: 150px;"
                                   wire:model.live="semanaFecha">
                        </div>
                        @else
                        <span class="badge bg-secondary" style="font-size:0.7rem;">Semana actual</span>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="ventasSemanalesChart" wire:ignore
                        data-dias='@json($ventasSemanales["dias"])'
                        data-ventas='@json($ventasSemanales["ventas"])'
                        height="250"></canvas>
                </div>
            </div>
        </div>

        {{-- Productos vendidos hoy --}}
        <div class="col-lg-6">
            <div class="card" style="min-height: 350px;">
                <div class="card-header pb-2 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Productos vendidos hoy</h5>
                    <span class="badge bg-primary rounded-pill">{{ count($productosVendidosHoy) }} productos</span>
                </div>
                <div class="card-body p-0">
                    @if(count($productosVendidosHoy) > 0)
                    <div style="max-height: 280px; overflow-y: auto;">
                        <table class="table table-sm table-hover mb-0">
                            <thead style="position: sticky; top: 0; background: #2b2b4b; color: #fff; z-index: 1;">
                                <tr>
                                    <th class="ps-3 py-2" style="font-size:0.75rem; font-weight:600;">PRODUCTO</th>
                                    <th class="text-center py-2" style="font-size:0.75rem; font-weight:600;">CANT.</th>
                                    <th class="text-end pe-3 py-2" style="font-size:0.75rem; font-weight:600;">TOTAL</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($productosVendidosHoy as $prod)
                                <tr>
                                    <td class="ps-3 py-2" style="font-size:0.875rem;">{{ $prod['nombre'] }}</td>
                                    <td class="text-center py-2">
                                        <span class="badge bg-success">{{ $prod['cantidad'] }}</span>
                                    </td>
                                    <td class="text-end pe-3 py-2" style="font-size:0.875rem;">
                                        <strong>{{ number_format($prod['total'], 2) }}</strong>
                                        <small class="text-muted ms-1">Bs</small>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="d-flex flex-column align-items-center justify-content-center py-5 text-muted">
                        <i class="fa-solid fa-cart-shopping fa-3x mb-3 opacity-25"></i>
                        <p class="mb-0">Sin ventas hoy</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

    </div>

    {{-- ── Ventas mensuales ─────────────────────────────────────────────── --}}
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header pb-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Ventas mensuales</h5>
                        <select class="form-select form-select-sm" style="width: 100px;" wire:model.live="anioMensual">
                            @foreach($anosDisponibles as $anio)
                            <option value="{{ $anio }}">{{ $anio }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="ventasMensualesChart" wire:ignore
                        data-meses='@json($meses["meses"])'
                        data-ventas='@json($meses["ventas"])'
                        data-anio="{{ $anioMensual }}"
                        height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- Scripts ────────────────────────────────────────────────────── --}}
    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    let chartSemanal, chartMensual;

    function crearGraficoSemanal(datosNuevos = null) {
        if (chartSemanal) { chartSemanal.destroy(); }

        const canvas = document.getElementById('ventasSemanalesChart');
        if (!canvas) return;

        const dias   = datosNuevos ? datosNuevos.dias   : JSON.parse(canvas.dataset.dias   || '[]');
        const ventas = datosNuevos ? datosNuevos.ventas : JSON.parse(canvas.dataset.ventas || '[]');

        chartSemanal = new Chart(canvas.getContext('2d'), {
            type: 'line',
            data: {
                labels: dias,
                datasets: [{
                    label: 'Ventas',
                    data: ventas,
                    borderColor: '#3B82F6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#3B82F6',
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(0,0,0,0.8)',
                        padding: 12,
                        callbacks: {
                            label: ctx => 'Bs. ' + ctx.parsed.y.toLocaleString('es-BO', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: v => v >= 1000 ? 'Bs. ' + (v / 1000).toFixed(0) + 'k' : 'Bs. ' + v.toFixed(0)
                        }
                    }
                }
            }
        });
    }

    function crearGraficoMensual(datosNuevos = null, anioNuevo = null) {
        if (chartMensual) { chartMensual.destroy(); }

        const canvas = document.getElementById('ventasMensualesChart');
        if (!canvas) return;

        const meses  = datosNuevos ? datosNuevos.meses  : JSON.parse(canvas.dataset.meses  || '[]');
        const ventas = datosNuevos ? datosNuevos.ventas : JSON.parse(canvas.dataset.ventas || '[]');
        const anio   = anioNuevo  ?? canvas.dataset.anio ?? '';

        chartMensual = new Chart(canvas.getContext('2d'), {
            type: 'bar',
            data: {
                labels: meses,
                datasets: [{
                    label: 'Ventas',
                    data: ventas,
                    backgroundColor: '#3B82F6',
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(0,0,0,0.8)',
                        padding: 12,
                        callbacks: {
                            title: ctx => ctx[0].label + ' ' + anio,
                            label: ctx => 'Bs. ' + ctx.parsed.y.toLocaleString('es-BO', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: v => v >= 1000 ? 'Bs. ' + (v / 1000).toFixed(0) + 'k' : 'Bs. ' + v.toFixed(0)
                        }
                    }
                }
            }
        });
    }

    function crearGraficos() {
        Chart.defaults.font.family = "'Nunito Sans', sans-serif";
        Chart.defaults.color = '#6c757d';
        crearGraficoSemanal();
        crearGraficoMensual();
    }

    document.addEventListener('DOMContentLoaded', crearGraficos);

    document.addEventListener('livewire:initialized', () => {
        Livewire.on('actualizarGraficoSemanal', event => {
            setTimeout(() => crearGraficoSemanal(event.datos), 150);
        });
        Livewire.on('actualizarGraficoMensual', event => {
            setTimeout(() => crearGraficoMensual(event.datos, event.anio), 150);
        });
    });
</script>
@endpush

</div>
