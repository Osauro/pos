<div>
    <div class="container-fluid" style="padding-top: 0 !important;">
        <div class="row starter-main" style="margin-top: 0 !important;">
            <div class="col-sm-12" style="padding-top: 0 !important;">
                <div class="card" style="margin-top: 0 !important;">

                    {{-- Header desktop --}}
                    <div class="card-header card-no-border pb-2 d-none d-md-block">
                        <div class="header-top d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <h3 class="mb-0">
                                <i class="fa-solid fa-store me-2"></i>Gestión de Negocios
                            </h3>
                            <div class="nav-item" style="max-width: 400px; width: 100%;">
                                <div class="input-group">
                                    <input type="text" class="form-control" placeholder="Buscar negocio..."
                                        wire:model.live="search">
                                    <button class="btn btn-primary" wire:click="createTenant" title="Nuevo negocio">
                                        <i class="fa-solid fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Header mobile sticky --}}
                    <div class="card-header card-no-border d-md-none"
                        style="position: sticky; top: 70px; z-index: 1030; background: white; box-shadow: 0 2px 4px rgba(0,0,0,.1); padding: 8px 12px;">
                        <div class="input-group">
                            <input type="text" class="form-control" placeholder="Buscar negocio..."
                                wire:model.live="search">
                            <button class="btn btn-primary" wire:click="createTenant">
                                <i class="fa-solid fa-plus"></i>
                            </button>
                        </div>
                    </div>

                    {{-- Cuerpo: grid de tarjetas --}}
                    <div class="card-body pt-0 mt-2 pb-3">

                        <div class="row g-1">
                            @forelse($tenants as $tenant)
                                @php
                                    $diasRestantes = $tenant->bill_date
                                        ? (int) \Carbon\Carbon::now()->diffInDays($tenant->bill_date, false)
                                        : null;
                                    $badgeColor = $diasRestantes === null
                                        ? 'secondary'
                                        : ($diasRestantes < 0 ? 'danger'
                                            : ($diasRestantes <= 7 ? 'warning' : 'success'));
                                    $estaActivo = $tenant->status === 'activo';
                                @endphp
                                <div class="col-12 col-sm-6 col-md-4 col-lg-3" wire:key="tenant-{{ $tenant->id }}">
                                    <div class="card h-100 border-2 {{ $estaActivo ? 'border-success' : 'border-danger' }}">

                                        {{-- Card header con nombre y acciones --}}
                                        <div class="card-header {{ $estaActivo ? 'bg-success' : 'bg-danger' }} text-white py-1 px-2">
                                            <div class="d-flex justify-content-between align-items-center gap-1">
                                                <small class="fw-bold text-truncate mb-0" style="font-size:0.72rem;">
                                                    {{ $tenant->nombre }}
                                                </small>
                                                <div class="d-flex gap-1 flex-shrink-0">
                                                    <button class="btn btn-light btn-sm py-0 px-1 text-dark"
                                                        style="font-size:0.65rem;"
                                                        wire:click="editTenant({{ $tenant->id }})"
                                                        title="Editar">
                                                        <i class="fa-solid fa-pen"></i>
                                                    </button>
                                                    <button class="btn btn-danger btn-sm py-0 px-1"
                                                        style="font-size:0.65rem;"
                                                        wire:click="deleteTenant({{ $tenant->id }})"
                                                        title="Eliminar">
                                                        <i class="fa-solid fa-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Card body compacto --}}
                                        <div class="card-body py-2 px-2" style="font-size:0.75rem;">

                                            <div class="mb-1">
                                                <small class="text-muted" style="font-size:0.65rem;">
                                                    ID: #{{ $tenant->id }}
                                                </small>
                                            </div>

                                            @if($tenant->telefono)
                                                <div class="mb-1">
                                                    <small class="text-muted">
                                                        <i class="fa-solid fa-phone me-1"></i>{{ $tenant->telefono }}
                                                    </small>
                                                </div>
                                            @endif

                                            {{-- Badge suscripción --}}
                                            <div class="mb-2">
                                                @if($tenant->bill_date)
                                                    @php
                                                        $esTrial = $tenant->bill_date->lte($tenant->created_at->addMonth()->addDay());
                                                    @endphp
                                                    <span class="badge bg-{{ $badgeColor }}"
                                                        style="font-size:0.6rem;"
                                                        title="Vence: {{ $tenant->bill_date->format('d/m/Y') }}">
                                                        <i class="fa-solid fa-{{ $esTrial ? 'clock' : 'crown' }} me-1"></i>
                                                        @if($esTrial) Prueba @else Anual @endif
                                                        @if($diasRestantes < 0)
                                                            · Vencido
                                                        @elseif($diasRestantes == 0)
                                                            · Hoy
                                                        @else
                                                            · {{ $diasRestantes }}d
                                                        @endif
                                                    </span>
                                                @else
                                                    <span class="badge bg-secondary" style="font-size:0.6rem;">Sin plan</span>
                                                @endif
                                            </div>

                                            {{-- Usuarios y estado --}}
                                            <div class="row g-1 mb-2">
                                                <div class="col-6">
                                                    <div class="text-center p-1 bg-light rounded">
                                                        <small class="text-muted d-block" style="font-size:0.6rem;">Usuarios</small>
                                                        <strong class="text-dark" style="font-size:0.75rem;">
                                                            {{ $tenant->users_count }}
                                                        </strong>
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="text-center p-1 bg-light rounded">
                                                        <small class="text-muted d-block" style="font-size:0.6rem;">Estado</small>
                                                        <div class="form-check form-switch d-flex justify-content-center mb-0">
                                                            <input class="form-check-input" type="checkbox"
                                                                wire:click="toggleStatus({{ $tenant->id }})"
                                                                {{ $estaActivo ? 'checked' : '' }}>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12 text-center py-5">
                                    <i class="fa-solid fa-store-slash fa-5x mb-3 text-muted opacity-25"></i>
                                    <p class="h5 text-muted mb-0">No se encontraron negocios</p>
                                </div>
                            @endforelse
                        </div>

                        <div class="mt-3">
                            {{ $tenants->links() }}
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($isOpenTenant)
    <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,.5)">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">
                        <i class="fa-solid fa-store me-2"></i>
                        {{ $tenant_id ? 'Editar negocio' : 'Nuevo negocio' }}
                    </h5>
                    <button type="button" class="btn-close" wire:click="$set('isOpenTenant', false)"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nombre del negocio *</label>
                        <input type="text" class="form-control @error('nombre') is-invalid @enderror"
                            wire:model="nombre" placeholder="Ej: Restaurante El Buen Sabor">
                        @error('nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Teléfono</label>
                        <input type="text" class="form-control" wire:model="telefono" placeholder="Ej: 76543210">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Dirección</label>
                        <input type="text" class="form-control" wire:model="direccion" placeholder="Ej: Av. Principal #123">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Estado</label>
                        <select class="form-select" wire:model="status">
                            <option value="activo">Activo</option>
                            <option value="inactivo">Inactivo</option>
                            <option value="suspendido">Suspendido</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Color de la Tienda</label>
                        @php
                            $temas = [
                                2  => ['color' => '#f73164', 'nombre' => 'Rosa'],
                                3  => ['color' => '#29adb2', 'nombre' => 'Teal'],
                                4  => ['color' => '#6610f2', 'nombre' => 'Morado'],
                                5  => ['color' => '#dc3545', 'nombre' => 'Rojo'],
                                6  => ['color' => '#f57f17', 'nombre' => 'Naranja'],
                                7  => ['color' => '#0288d1', 'nombre' => 'Azul'],
                                8  => ['color' => '#00897b', 'nombre' => 'Verde Teal'],
                                9  => ['color' => '#558b2f', 'nombre' => 'Verde'],
                                10 => ['color' => '#455a64', 'nombre' => 'Gris Azul'],
                            ];
                        @endphp
                        <div class="d-flex flex-wrap gap-2 mt-1">
                            @foreach($temas as $num => $tema)
                            <button type="button"
                                wire:click="$set('theme_number', {{ $num }})"
                                title="{{ $tema['nombre'] }}"
                                style="
                                    width:32px;height:32px;border-radius:50%;
                                    background:{{ $tema['color'] }};
                                    border:3px solid {{ $theme_number == $num ? '#333' : 'transparent' }};
                                    outline:2px solid {{ $theme_number == $num ? $tema['color'] : 'transparent' }};
                                    outline-offset:2px;cursor:pointer;transition:all .15s;
                                ">
                            </button>
                            @endforeach
                        </div>
                    </div>
                    @if(!$tenant_id)
                    <div class="alert alert-info small mb-0 py-2">
                        <i class="fa-solid fa-gift me-1"></i>
                        El negocio tendrá <strong>1 mes de prueba gratuita</strong>.
                        Después puede renovar por <strong>50 Bs/año</strong>.
                    </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="$set('isOpenTenant', false)">
                        Cancelar
                    </button>
                    <button type="button" class="btn btn-primary" wire:click="saveTenant" wire:loading.attr="disabled">
                        <span wire:loading wire:target="saveTenant" class="spinner-border spinner-border-sm me-1"></span>
                        Guardar
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
