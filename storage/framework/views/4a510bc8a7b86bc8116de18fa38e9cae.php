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
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $productos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $producto): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                <div class="col-lg-4 col-md-6 col-sm-12">
                    <div class="card mb-0 shadow-sm producto-card position-relative"
                        style="opacity: <?php echo e($producto->estado ? '1' : '0.5'); ?>; transition: opacity 0.3s;">
                        <div class="card-body p-2">
                            <div class="d-flex align-items-start">
                                <!-- Botones en esquina superior derecha -->
                                <div class="position-absolute top-0 end-0 d-flex gap-1" style="z-index: 10;">
                                    <button class="btn btn-sm btn-primary" wire:click="edit(<?php echo e($producto->id); ?>)"
                                        title="Editar">
                                        <i class="fa-solid fa-pen"></i>
                                    </button>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($puedeEliminar): ?>
                                    <button class="btn btn-sm btn-danger" wire:click="delete(<?php echo e($producto->id); ?>)"
                                        title="Eliminar">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>

                                <!-- Imagen del producto -->
                                <div class="flex-shrink-0 me-3">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($producto->imagen): ?>
                                        <img src="<?php echo e(asset('storage/' . $producto->imagen)); ?>"
                                            alt="<?php echo e($producto->nombre); ?>" class="rounded"
                                            style="width: 80px; height: 80px; object-fit: cover; border: 2px solid #e9ecef;">
                                    <?php else: ?>
                                        <div class="rounded d-flex align-items-center justify-content-center"
                                            style="width: 80px; height: 80px; background-color: #f8f9fa; border: 2px solid #e9ecef;">
                                            <i class="fa-solid fa-image fa-2x text-muted"></i>
                                        </div>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>

                                <!-- Información del producto -->
                                <div class="flex-grow-1">
                                    <h6 class="mb-1 fw-semibold">
                                        <?php echo e($producto->nombre); ?>

                                    </h6>
                                    <div class="small mb-2">
                                        <span class="badge bg-secondary"><?php echo e($producto->tipo); ?></span>
                                    </div>
                                    <div class="text-success fw-bold fs-5">
                                        Bs. <?php echo e(number_format($producto->precio, 2)); ?>

                                    </div>
                                </div>
                            </div>

                            <!-- Botón visibilidad - Flotante inferior derecha -->
                            <div class="position-absolute bottom-0 end-0 p-1">
                                <button wire:click="toggleEstado(<?php echo e($producto->id); ?>)"
                                    class="btn btn-sm <?php echo e($producto->estado ? 'btn-success' : 'btn-secondary'); ?>"
                                    style="padding: 2px 6px; font-size: 0.7rem;"
                                    title="<?php echo e($producto->estado ? 'Activo' : 'Inactivo'); ?>">
                                    <i class="fa-solid <?php echo e($producto->estado ? 'fa-eye' : 'fa-eye-slash'); ?>"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                <div class="col-12">
                    <div class="text-center py-5 empty-state">
                        <i class="fa-solid fa-box-open fa-5x mb-3 text-muted"></i>
                        <p class="h5 text-muted mb-0">No se encontraron resultados</p>
                    </div>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>

    <!-- Modal para Crear/Editar Producto -->
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isOpen): ?>
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">
                            <i class="fa-solid fa-box me-2"></i>
                            <?php echo e($producto_id ? 'Editar' : 'Nuevo'); ?> Producto
                        </h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="closeModal"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="<?php echo e($producto_id ? 'update' : 'store'); ?>">
                            <div class="row">
                                <!-- Columna izquierda: Imagen clickeable -->
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Imagen del Producto</label>
                                    <div class="border rounded p-2 text-center position-relative"
                                        style="min-height: 160px; display: flex; align-items: center; justify-content: center; background-color: #f8f9fa; cursor: pointer;"
                                        onclick="document.getElementById('new_imagen').click()">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($new_imagen): ?>
                                            <img src="<?php echo e($new_imagen->temporaryUrl()); ?>" alt="Preview"
                                                style="max-width: 100%; max-height: 300px; object-fit: contain; border-radius: 8px;">
                                        <?php elseif($imagen): ?>
                                            <img src="<?php echo e(asset('storage/' . $imagen)); ?>" alt="Imagen actual"
                                                style="max-width: 100%; max-height: 300px; object-fit: contain; border-radius: 8px;">
                                        <?php else: ?>
                                            <div class="text-center">
                                                <i class="fa-solid fa-cloud-arrow-up fa-4x text-muted mb-2"></i>
                                                <p class="text-muted small mb-0">Click para seleccionar imagen</p>
                                            </div>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                    <!-- Input file oculto -->
                                    <input type="file" class="d-none" wire:model="new_imagen" id="new_imagen"
                                        accept="image/*">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['new_imagen'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <div class="text-danger small mt-1"><?php echo e($message); ?></div>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>

                                <!-- Columna derecha: Inputs -->
                                <div class="col-md-8">
                                    <!-- Nombre -->
                                    <div class="mb-3">
                                        <label for="nombre" class="form-label">Nombre <span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control <?php $__errorArgs = ['nombre'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                            wire:model="nombre" id="nombre" placeholder="Ej: Pollo a la Brasa">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['nombre'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                            <div class="invalid-feedback"><?php echo e($message); ?></div>
                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>

                                    <!-- Precio -->
                                    <div class="mb-3">
                                        <label for="precio" class="form-label">Precio (Bs.) <span
                                                class="text-danger">*</span></label>
                                        <input type="number" step="0.01"
                                            class="form-control <?php $__errorArgs = ['precio'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                            wire:model="precio" id="precio" placeholder="0.00">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['precio'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                            <div class="invalid-feedback"><?php echo e($message); ?></div>
                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
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
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['tipo'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                            <div class="text-danger small mt-1"><?php echo e($message); ?></div>
                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeModal">Cancelar</button>
                        <button type="button" class="btn btn-primary"
                            wire:click="<?php echo e($producto_id ? 'update' : 'store'); ?>" wire:loading.attr="disabled">
                            <span wire:loading.remove>
                                <?php echo e($producto_id ? 'Actualizar' : 'Guardar'); ?>

                            </span>
                            <span wire:loading>
                                <i class="fa-solid fa-spinner fa-spin me-1"></i> Procesando...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php echo $__env->make('partials.paginate-bar', ['results' => $productos, 'storageKey' => 'productos'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</div>
<?php /**PATH C:\laragon\www\tpv\resources\views/livewire/productos.blade.php ENDPATH**/ ?>