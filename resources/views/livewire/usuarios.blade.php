<div>
    <!-- Header -->
                    <div class="module-sticky-header">
                        <div class="d-flex justify-content-between align-items-center gap-2">
                            <h5 class="mb-0 fw-bold">Usuarios</h5>
                            <button class="btn btn-primary" wire:click="create">
                                <i class="fa-solid fa-plus"></i>
                            </button>
                        </div>
    </div>

    <div class="module-scroll-area">
        <div class="row g-2 p-2">
                            @forelse($usuarios as $usuario)
                                <div class="col-lg-4 col-md-6 col-sm-12">
                                    <div class="card mb-0 shadow-sm producto-card">
                                        <div class="card-body p-3">
                                            <div class="d-flex align-items-start position-relative">
                                                <!-- Botones en esquina superior derecha -->
                                                <div class="position-absolute top-0 end-0 d-flex gap-1">
                                                    <button class="btn btn-sm btn-warning"
                                                        wire:click="resetPin({{ $usuario->id }})" title="Resetear PIN">
                                                        <i class="fa-solid fa-key"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-primary"
                                                        wire:click="edit({{ $usuario->id }})" title="Editar">
                                                        <i class="fa-solid fa-pen"></i>
                                                    </button>
                                                    @if($puedeEliminar)
                                                    <button class="btn btn-sm btn-danger"
                                                        wire:click="delete({{ $usuario->id }})"
                                                        title="Eliminar">
                                                        <i class="fa-solid fa-trash"></i>
                                                    </button>
                                                    @endif
                                                </div>

                                                <!-- Avatar generado con iniciales -->
                                                <div class="flex-shrink-0 me-3">
                                                    <img src="https://ui-avatars.com/api/?name={{ urlencode($usuario->nombre) }}&background=884A39&color=fff&size=60&bold=true&rounded=true"
                                                        alt="{{ $usuario->nombre }}" class="rounded-circle"
                                                        style="width: 60px; height: 60px; object-fit: cover; border: 2px solid #e9ecef;">
                                                </div>

                                                <!-- Información del usuario -->
                                                <div class="flex-grow-1">
                                                    <h6 class="mb-1 fw-semibold">{{ $usuario->nombre }}</h6>
                                                    <div class="small">
                                                        <i class="fa-solid fa-phone text-primary me-1"></i>
                                                        <span class="text-muted">{{ $usuario->celular }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-footer bg-light py-2 px-3">
                                            <div class="d-flex justify-content-between align-items-center small">
                                                <div>
                                                    @php $rolTenant = $usuario->tenants->first()?->pivot->role ?? 'operador'; @endphp
                                                    @if ($rolTenant === 'admin')
                                                        <span class="badge bg-danger">
                                                            <i class="fa-solid fa-crown me-1"></i>Admin
                                                        </span>
                                                    @else
                                                        <span class="badge bg-primary">
                                                            <i class="fa-solid fa-user-tie me-1"></i>Operador
                                                        </span>
                                                    @endif
                                                </div>
                                                <span class="text-muted">
                                                    <i class="fa-solid fa-calendar me-1"></i>{{ $usuario->created_at->format('d/m/Y') }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12">
                                    <div class="text-center py-5 empty-state">
                                        <i class="fa-solid fa-user-slash fa-5x mb-3 text-muted"></i>
                                        <p class="h5 text-muted mb-0">No se encontraron resultados</p>
                                    </div>
                                </div>
                            @endforelse
        </div>
    </div>

    <!-- Modal para Crear/Editar Usuario -->
    @if ($isOpen)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">
                            <i class="fa-solid fa-user me-2"></i>
                            {{ $usuario_id ? 'Editar Usuario' : 'Nuevo Usuario' }}
                        </h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="closeModal"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="{{ $usuario_id ? 'update' : 'store' }}">
                            <!-- Nombre -->
                            <div class="mb-3">
                                <label class="form-label">Nombre <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('nombre') is-invalid @enderror"
                                    wire:model="nombre" placeholder="Ej: Juan Pérez">
                                @error('nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <!-- Celular -->
                            <div class="mb-3">
                                <label class="form-label">Celular <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('celular') is-invalid @enderror"
                                    wire:model="celular" placeholder="Ej: 71234567" maxlength="8">
                                @error('celular') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <!-- PIN eliminado del formulario - se genera automáticamente al guardar -->

                            <!-- Tipo -->
                            <div class="mb-3">
                                <label class="form-label">Tipo <span class="text-danger">*</span></label>
                                <select class="form-select @error('tipo') is-invalid @enderror" wire:model="tipo">
                                    <option value="user">Usuario</option>
                                    <option value="admin">Admin</option>
                                </select>
                                @error('tipo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeModal">Cancelar</button>
                        <button type="button" class="btn btn-primary"
                            wire:click="{{ $usuario_id ? 'update' : 'store' }}" wire:loading.attr="disabled">
                            <span wire:loading.remove>{{ $usuario_id ? 'Actualizar' : 'Guardar' }}</span>
                            <span wire:loading><i class="fa-solid fa-spinner fa-spin me-1"></i> Procesando...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal: Confirmar asociación de usuario existente --}}
    @if($confirmarAsociacion)
    <div class="modal fade show d-block" tabindex="-1" wire:click.self="cancelarAsociacion" style="background-color:rgba(0,0,0,0.6);">
        <div class="modal-dialog modal-dialog-centered" style="max-width:420px;">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark border-0 pb-2">
                    <h5 class="modal-title fw-bold d-flex align-items-center gap-2">
                        <i class="fa-solid fa-user-plus"></i> Usuario ya registrado
                    </h5>
                    <button type="button" class="btn-close" wire:click="cancelarAsociacion"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <div style="font-size:3rem; color:#f0ad4e; margin-bottom:12px;">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>
                    <p class="mb-1">El celular ingresado ya pertenece a:</p>
                    <p class="fw-bold fs-5 mb-3">{{ $nombreExistente }}</p>
                    <p class="text-muted" style="font-size:0.9rem;">¿Deseas añadirlo a esta tienda como <strong>{{ ucfirst($tipo) }}</strong>?</p>
                </div>
                <div class="modal-footer border-0 pt-0 justify-content-center gap-2">
                    <button type="button" class="btn btn-secondary" wire:click="cancelarAsociacion">
                        Cancelar
                    </button>
                    <button type="button" class="btn btn-success" wire:click="asociarUsuarioConfirmado" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="asociarUsuarioConfirmado">
                            <i class="fa-solid fa-user-plus me-1"></i> Sí, asociar
                        </span>
                        <span wire:loading wire:target="asociarUsuarioConfirmado">
                            <i class="fa-solid fa-spinner fa-spin me-1"></i> Asociando...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    @include('partials.paginate-bar', ['results' => $usuarios, 'storageKey' => 'usuarios'])
</div>

