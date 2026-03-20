<div>
    <!-- Header (escritorio y móvil) -->
                    <div class="module-sticky-header">
                        <div class="d-flex justify-content-between align-items-center gap-2 flex-wrap">
                            <h5 class="mb-0 fw-bold">Turnos</h5>
                            <div class="d-flex align-items-center gap-2">
                                <select wire:model.live="year" class="form-select" style="width:110px">
                                    @foreach(range(2024, date('Y') + 1) as $y)
                                        <option value="{{ $y }}">{{ $y }}</option>
                                    @endforeach
                                </select>
                                <button class="btn btn-primary" wire:click="create" title="Nuevo Turno">
                                    <i class="fa-solid fa-plus"></i>
                                </button>
                            </div>
                        </div>
    </div>

    <div class="module-scroll-area">
        <div class="row g-2 p-2">
                            @forelse($turnos as $turno)
                                <div class="col-lg-4 col-md-6 col-sm-12">
                                    <div class="card mb-0 shadow-sm producto-card">
                                        <div class="card-body p-3">
                                            <div class="d-flex align-items-start position-relative">

                                                <!-- Avatar del encargado -->
                                                <div class="flex-shrink-0 me-3">
                                                    <img src="https://ui-avatars.com/api/?name={{ urlencode($turno->encargado->nombre) }}&background=27ae60&color=fff&size=60&bold=true&rounded=true"
                                                        alt="{{ $turno->encargado->nombre }}" class="rounded-circle"
                                                        style="width: 60px; height: 60px; object-fit: cover; border: 2px solid #e9ecef;">
                                                </div>

                                                <!-- Información del turno -->
                                                <div class="flex-grow-1">
                                                    <h6 class="mb-1 fw-semibold">{{ $turno->encargado->nombre }}</h6>
                                                    <div class="small text-muted">
                                                        <i class="fa-solid fa-calendar-days me-1 text-success"></i>
                                                        {{ $turno->fecha_inicio->format('d/m/Y') }}
                                                        <i class="fa-solid fa-arrow-right mx-1"></i>
                                                        {{ $turno->fecha_fin->format('d/m/Y') }}
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                        <div class="card-footer bg-light py-2 px-3">
                                            <div class="d-flex justify-content-between align-items-center small">
                                                <div class="d-flex gap-1 align-items-center">
                                                    <span class="badge bg-success">
                                                        <i class="fa-solid fa-hashtag me-1"></i>Semana {{ $turno->fecha_inicio->isoWeek }}
                                                    </span>
                                                    @if($turno->estadoReal === 'activo')
                                                        <span class="badge bg-primary">
                                                            <i class="fa-solid fa-circle-check me-1"></i>Activo
                                                        </span>
                                                    @else
                                                        <span class="badge bg-secondary">
                                                            <i class="fa-solid fa-circle-xmark me-1"></i>Finalizado
                                                        </span>
                                                    @endif
                                                </div>
                                                <span class="text-muted">
                                                    <i class="fa-solid fa-clock me-1"></i>
                                                    {{ $turno->fecha_inicio->diffInDays($turno->fecha_fin) + 1 }} días
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12">
                                    <div class="text-center py-5 empty-state">
                                        <i class="fa-solid fa-calendar-xmark fa-5x mb-3 text-muted"></i>
                                        <p class="h5 text-muted mb-0">No hay turnos registrados en {{ $year }}</p>
                                    </div>
                                </div>
                            @endforelse
        </div>
    </div>

    @include('partials.paginate-bar', ['results' => $turnos, 'storageKey' => 'turnos'])

    <!-- Modal Nuevo Turno (solo encargados, clic = guardar) -->
    @if($isOpen)
    <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="fa-solid fa-calendar-days me-2"></i>
                        Nuevo Turno
                    </h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="closeModal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small mb-3">
                        <i class="fa-solid fa-calendar-week me-1"></i>
                        Semana del <strong>{{ \Carbon\Carbon::now()->startOfWeek(\Carbon\Carbon::MONDAY)->format('d/m/Y') }}</strong>
                        al <strong>{{ \Carbon\Carbon::now()->startOfWeek(\Carbon\Carbon::MONDAY)->addDays(6)->format('d/m/Y') }}</strong>
                    </p>
                    <div class="d-flex flex-wrap gap-2 justify-content-center">
                        @foreach($usuarios as $usuario)
                            <button type="button"
                                wire:click="quickStore({{ $usuario->id }})"
                                class="btn btn-outline-secondary d-flex flex-column align-items-center p-3"
                                style="min-width:90px;border-radius:14px">
                                <img src="https://ui-avatars.com/api/?name={{ urlencode($usuario->nombre) }}&background=884A39&color=fff&size=56&bold=true&rounded=true"
                                    class="rounded-circle mb-2" style="width:56px;height:56px">
                                <small class="fw-semibold text-center" style="font-size:0.78rem;line-height:1.3">{{ $usuario->nombre }}</small>
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
