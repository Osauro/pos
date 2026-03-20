<div>
    <!-- Header -->
    <div class="module-sticky-header">
        <div class="d-flex justify-content-between align-items-center gap-2">
            <h5 class="mb-0 fw-bold">Productos</h5>
            <button class="btn btn-primary" wire:click="create">
                <i class="fa-solid fa-plus"></i>
            </button>
        </div>
    </div>

    <div class="module-scroll-area">
        <div class="row g-2 p-2">
            @forelse($productos as $producto)
                <div class="col-lg-4 col-md-6 col-sm-12">
                    <div class="card mb-0 shadow-sm producto-card position-relative"
                        style="opacity: {{ $producto->estado ? '1' : '0.5' }}; transition: opacity 0.3s;">
                        <div class="card-body p-2">
                            <div class="d-flex align-items-start">
                                <!-- Botones en esquina superior derecha -->
                                <div class="position-absolute top-0 end-0 d-flex gap-1" style="z-index: 10;">
                                    <button class="btn btn-sm btn-primary" wire:click="edit({{ $producto->id }})"
                                        title="Editar">
                                        <i class="fa-solid fa-pen"></i>
                                    </button>
                                    @if($puedeEliminar)
                                    <button class="btn btn-sm btn-danger" wire:click="delete({{ $producto->id }})"
                                        title="Eliminar">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                    @endif
                                </div>

                                <!-- Imagen del producto -->
                                <div class="flex-shrink-0 me-3">
                                    @if ($producto->imagen)
                                        <img src="{{ asset('storage/' . $producto->imagen) }}"
                                            alt="{{ $producto->nombre }}" class="rounded"
                                            style="width: 80px; height: 80px; object-fit: cover; border: 2px solid #e9ecef;">
                                    @else
                                        <div class="rounded d-flex align-items-center justify-content-center"
                                            style="width: 80px; height: 80px; background-color: #f8f9fa; border: 2px solid #e9ecef;">
                                            <i class="fa-solid fa-image fa-2x text-muted"></i>
                                        </div>
                                    @endif
                                </div>

                                <!-- Información del producto -->
                                <div class="flex-grow-1">
                                    <h6 class="mb-1 fw-semibold">
                                        {{ $producto->nombre }}
                                    </h6>
                                    <div class="small mb-2">
                                        <span class="badge bg-secondary">{{ $producto->tipo }}</span>
                                    </div>
                                    <div class="text-success fw-bold fs-5">
                                        Bs. {{ number_format($producto->precio, 2) }}
                                    </div>
                                </div>
                            </div>

                            <!-- Botón visibilidad - Flotante inferior derecha -->
                            <div class="position-absolute bottom-0 end-0 p-1">
                                <button wire:click="toggleEstado({{ $producto->id }})"
                                    class="btn btn-sm {{ $producto->estado ? 'btn-success' : 'btn-secondary' }}"
                                    style="padding: 2px 6px; font-size: 0.7rem;"
                                    title="{{ $producto->estado ? 'Activo' : 'Inactivo' }}">
                                    <i class="fa-solid {{ $producto->estado ? 'fa-eye' : 'fa-eye-slash' }}"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="text-center py-5 empty-state">
                        <i class="fa-solid fa-box-open fa-5x mb-3 text-muted"></i>
                        <p class="h5 text-muted mb-0">No se encontraron resultados</p>
                    </div>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Modal para Crear/Editar Producto -->
    @if ($isOpen)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">
                            <i class="fa-solid fa-box me-2"></i>
                            {{ $producto_id ? 'Editar' : 'Nuevo' }} Producto
                        </h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="closeModal"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="{{ $producto_id ? 'update' : 'store' }}">
                            <div class="row">
                                <!-- Columna izquierda: Imagen clickeable -->
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Imagen del Producto</label>
                                    <div class="border rounded p-2 text-center position-relative"
                                        style="min-height: 160px; display: flex; align-items: center; justify-content: center; background-color: #f8f9fa; cursor: pointer;"
                                        onclick="document.getElementById('new_imagen').click()">
                                        @if ($new_imagen)
                                            <img src="{{ $new_imagen->temporaryUrl() }}" alt="Preview"
                                                style="max-width: 100%; max-height: 300px; object-fit: contain; border-radius: 8px;">
                                        @elseif($imagen)
                                            <img src="{{ asset('storage/' . $imagen) }}" alt="Imagen actual"
                                                style="max-width: 100%; max-height: 300px; object-fit: contain; border-radius: 8px;">
                                        @else
                                            <div class="text-center">
                                                <i class="fa-solid fa-cloud-arrow-up fa-4x text-muted mb-2"></i>
                                                <p class="text-muted small mb-0">Click para seleccionar imagen</p>
                                            </div>
                                        @endif
                                    </div>
                                    <!-- Input file oculto -->
                                    <input type="file" class="d-none" wire:model="new_imagen" id="new_imagen"
                                        accept="image/*">
                                    @error('new_imagen')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Columna derecha: Inputs -->
                                <div class="col-md-8">
                                    <!-- Nombre -->
                                    <div class="mb-3">
                                        <label for="nombre" class="form-label">Nombre <span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('nombre') is-invalid @enderror"
                                            wire:model="nombre" id="nombre" placeholder="Ej: Pollo a la Brasa">
                                        @error('nombre')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Precio -->
                                    <div class="mb-3">
                                        <label for="precio" class="form-label">Precio (Bs.) <span
                                                class="text-danger">*</span></label>
                                        <input type="number" step="0.01"
                                            class="form-control @error('precio') is-invalid @enderror"
                                            wire:model="precio" id="precio" placeholder="0.00">
                                        @error('precio')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Tipo -->
                                    <div class="mb-3">
                                        <label class="form-label">Tipo <span class="text-danger">*</span></label>
                                        <div class="btn-group w-100" role="group">
                                            <input type="radio" class="btn-check" wire:model="tipo" value="Platos"
                                                id="tipo-platos" autocomplete="off">
                                            <label class="btn btn-outline-primary tipo-btn" for="tipo-platos">
                                                <i class="fa-solid fa-utensils me-1"></i> Platos
                                            </label>

                                            <input type="radio" class="btn-check" wire:model="tipo"
                                                value="Refrescos" id="tipo-refrescos" autocomplete="off">
                                            <label class="btn btn-outline-primary tipo-btn" for="tipo-refrescos">
                                                <i class="fa-solid fa-glass-water me-1"></i> Refrescos
                                            </label>

                                            <input type="radio" class="btn-check" wire:model="tipo"
                                                value="Porciones" id="tipo-porciones" autocomplete="off">
                                            <label class="btn btn-outline-primary tipo-btn" for="tipo-porciones">
                                                <i class="fa-solid fa-bowl-food me-1"></i> Porciones
                                            </label>
                                        </div>
                                        @error('tipo')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeModal">Cancelar</button>
                        <button type="button" class="btn btn-primary"
                            wire:click="{{ $producto_id ? 'update' : 'store' }}" wire:loading.attr="disabled">
                            <span wire:loading.remove>
                                {{ $producto_id ? 'Actualizar' : 'Guardar' }}
                            </span>
                            <span wire:loading>
                                <i class="fa-solid fa-spinner fa-spin me-1"></i> Procesando...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @include('partials.paginate-bar', ['results' => $productos, 'storageKey' => 'productos'])
</div>
