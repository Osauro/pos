<div>
    <div class="container-fluid" style="padding-top: 0 !important;">
        <div class="row starter-main" style="margin-top: 0 !important;">
            <div class="col-sm-12" style="padding-top: 0 !important;">

                {{-- Encabezado --}}
                <div class="d-flex align-items-center gap-2 mb-3">
                    <i class="fa-solid fa-gauge-high fa-lg text-primary"></i>
                    <h4 class="mb-0 fw-bold">Dashboard</h4>
                </div>

                {{-- Tarjetas de métricas --}}
                <div class="row g-3 mb-4">
                    <div class="col-6 col-md-4 col-lg-2">
                        <div class="card border-0 shadow-sm text-center p-3 h-100">
                            <div class="fw-bold" style="font-size:1.6rem; color:var(--theme-default);">{{ $totalTenants }}</div>
                            <small class="text-muted">Total negocios</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-4 col-lg-2">
                        <div class="card border-0 shadow-sm text-center p-3 h-100">
                            <div class="fw-bold text-success" style="font-size:1.6rem;">{{ $tenantsActivos }}</div>
                            <small class="text-muted">Activos</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-4 col-lg-2">
                        <div class="card border-0 shadow-sm text-center p-3 h-100">
                            <div class="fw-bold text-danger" style="font-size:1.6rem;">{{ $tenantsVencidos }}</div>
                            <small class="text-muted">Vencidos</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-4 col-lg-2">
                        <div class="card border-0 shadow-sm text-center p-3 h-100">
                            <div class="fw-bold text-warning" style="font-size:1.6rem;">{{ $tenantsProximos }}</div>
                            <small class="text-muted">Vencen en 7d</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-4 col-lg-2">
                        <div class="card border-0 shadow-sm text-center p-3 h-100">
                            @if($pagosPendientes > 0)
                                <a href="{{ route('admin.pagos') }}" class="text-decoration-none">
                                    <div class="fw-bold text-danger" style="font-size:1.6rem;">{{ $pagosPendientes }}</div>
                                    <small class="text-danger fw-semibold">Pagos pendientes</small>
                                </a>
                            @else
                                <div class="fw-bold text-muted" style="font-size:1.6rem;">0</div>
                                <small class="text-muted">Pagos pendientes</small>
                            @endif
                        </div>
                    </div>
                    <div class="col-6 col-md-4 col-lg-2">
                        <div class="card border-0 shadow-sm text-center p-3 h-100">
                            <div class="fw-bold text-success" style="font-size:1.6rem;">{{ number_format($ingresosMes, 0) }} Bs</div>
                            <small class="text-muted">Ingresos este mes</small>
                        </div>
                    </div>
                </div>

                <div class="row g-3">

                    {{-- Pagos pendientes --}}
                    <div class="col-12 col-lg-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-light d-flex justify-content-between align-items-center py-2">
                                <h6 class="mb-0 fw-bold">
                                    <i class="fa-solid fa-clock text-warning me-1"></i>Pagos pendientes
                                </h6>
                                <a href="{{ route('admin.pagos') }}" class="btn btn-sm btn-outline-primary py-0 px-2" style="font-size:0.7rem;">
                                    Ver todos
                                </a>
                            </div>
                            <div class="card-body py-2 px-2">
                                @forelse($ultimosPendientes as $pago)
                                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                    <div>
                                        <div class="fw-semibold" style="font-size:0.85rem;">{{ $pago->tenant->nombre }}</div>
                                        <small class="text-muted">{{ $pago->created_at->format('d/m/Y H:i') }}</small>
                                    </div>
                                    <a href="{{ route('admin.pagos') }}" class="btn btn-sm btn-success py-0 px-2" style="font-size:0.7rem;">
                                        <i class="fa-solid fa-check me-1"></i>Verificar
                                    </a>
                                </div>
                                @empty
                                <div class="text-center py-4 text-muted">
                                    <i class="fa-solid fa-check-circle fa-2x mb-2 text-success opacity-50"></i>
                                    <p class="mb-0 small">Sin pagos pendientes</p>
                                </div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    {{-- Próximos a vencer --}}
                    <div class="col-12 col-lg-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-light d-flex justify-content-between align-items-center py-2">
                                <h6 class="mb-0 fw-bold">
                                    <i class="fa-solid fa-triangle-exclamation text-warning me-1"></i>Próximos a vencer (7d)
                                </h6>
                                <a href="{{ route('admin.tenants') }}" class="btn btn-sm btn-outline-primary py-0 px-2" style="font-size:0.7rem;">
                                    Ver negocios
                                </a>
                            </div>
                            <div class="card-body py-2 px-2">
                                @forelse($proximosVencer as $t)
                                @php $dias = (int) now()->diffInDays($t->bill_date, false); @endphp
                                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                    <div>
                                        <div class="fw-semibold" style="font-size:0.85rem;">{{ $t->nombre }}</div>
                                        <small class="text-muted">Vence: {{ $t->bill_date->format('d/m/Y') }}</small>
                                    </div>
                                    <span class="badge bg-{{ $dias <= 3 ? 'danger' : 'warning' }}">{{ $dias }}d</span>
                                </div>
                                @empty
                                <div class="text-center py-4 text-muted">
                                    <i class="fa-solid fa-calendar-check fa-2x mb-2 text-success opacity-50"></i>
                                    <p class="mb-0 small">Ninguno vence pronto</p>
                                </div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    {{-- Últimos negocios registrados --}}
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-light py-2">
                                <h6 class="mb-0 fw-bold">
                                    <i class="fa-solid fa-store me-1 text-primary"></i>Últimos negocios registrados
                                </h6>
                            </div>
                            <div class="card-body py-0 px-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0" style="font-size:0.82rem;">
                                        <thead class="table-light">
                                            <tr>
                                                <th>#</th>
                                                <th>Nombre</th>
                                                <th>Estado</th>
                                                <th>Vencimiento</th>
                                                <th>Usuarios</th>
                                                <th>Registro</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($ultimosTenants as $t)
                                            <tr>
                                                <td class="text-muted">#{{ $t->id }}</td>
                                                <td class="fw-semibold">{{ $t->nombre }}</td>
                                                <td>
                                                    <span class="badge bg-{{ $t->status === 'activo' ? 'success' : 'danger' }}">
                                                        {{ ucfirst($t->status) }}
                                                    </span>
                                                </td>
                                                <td>{{ $t->bill_date ? $t->bill_date->format('d/m/Y') : '—' }}</td>
                                                <td>{{ $t->users->count() }}</td>
                                                <td>{{ $t->created_at->format('d/m/Y') }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
