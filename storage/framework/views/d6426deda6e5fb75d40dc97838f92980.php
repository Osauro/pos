<div>
    <!-- Header -->
                    <div class="module-sticky-header">
                        <div class="d-flex justify-content-between align-items-center gap-2">
                            <h5 class="mb-0 fw-bold">Ventas</h5>
                            <div class="d-flex align-items-center gap-2">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($mostrarFiltro): ?>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($turno_seleccionado): ?>
                                        <?php
                                            $turnoHeader = \App\Models\Turno::find($turno_seleccionado);
                                        ?>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($turnoHeader): ?>
                                            <button class="btn btn-danger" wire:click="limpiarFiltroFechas" title="Limpiar filtro">
                                                Turno: <?php echo e(\Carbon\Carbon::parse($turnoHeader->fecha_inicio)->format('d/m')); ?> - <?php echo e(\Carbon\Carbon::parse($turnoHeader->fecha_fin)->format('d/m')); ?>

                                            </button>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <button class="btn btn-outline-secondary" wire:click="abrirModalFiltro" title="Filtrar por fecha">
                                        <i class="fa-solid fa-calendar-days"></i>
                                    </button>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($puedeCrearVenta): ?>
                                    <button class="btn btn-primary" wire:click="crearVenta">
                                        <i class="fa-solid fa-plus"></i>
                                    </button>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </div>
    </div>

    <div class="module-scroll-area">
        <div class="row g-2 p-2">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $ventas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $venta): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                <div class="col-md-4 col-12">
                                    <div class="card mb-0 shadow-sm <?php echo e($venta->estado === 'Cancelado' ? 'opacity-50' : ''); ?>">
                                        <div class="card-body compra-card-body">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div class="flex-grow-1">
                                                    <!-- Header -->
                                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                                        <h4 class="mb-0 fw-bold">
                                                            Venta #<?php echo e($venta->numero_venta); ?>

                                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($venta->estado)): ?>
                                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($venta->estado === 'Cancelado'): ?>
                                                                    <span class="badge bg-danger ms-2">Cancelada</span>
                                                                <?php elseif($venta->estado === 'Completo'): ?>
                                                                    <span class="badge bg-success ms-2">Completa</span>
                                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                        </h4>
                                                        <div class="d-flex gap-1">
                                                            <button class="btn btn-sm btn-info"
                                                                wire:click="verDetalles(<?php echo e($venta->id); ?>)"
                                                                title="Ver detalles">
                                                                <i class="fa-solid fa-eye"></i>
                                                            </button>
                                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!isset($venta->estado) || $venta->estado !== 'Cancelado'): ?>
                                                                <button class="btn btn-sm btn-success"
                                                                    wire:click="imprimirTicket(<?php echo e($venta->id); ?>)"
                                                                    title="Imprimir ticket">
                                                                    <i class="fa-solid fa-receipt"></i>
                                                                </button>
                                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                        </div>
                                                    </div>

                                                    <!-- Avatar Group de productos -->
                                                    <div class="avatar-group mb-1">
                                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $venta->ventaItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                                            <div class="avatar"
                                                                title="<?php echo e($item->producto->nombre ?? 'Producto'); ?>">
                                                                <img src="<?php echo e($item->producto->photo_url); ?>"
                                                                    alt="<?php echo e($item->producto->nombre); ?>">
                                                                <span
                                                                    class="quantity-badge"><?php echo e($item->cantidad); ?></span>
                                                            </div>
                                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                                    </div>

                                                    <!-- Badges de totales -->
                                                    <div class="d-flex gap-2 flex-wrap mb-1">
                                                        <?php
                                                            $total = $venta->efectivo + $venta->online + $venta->credito;
                                                        ?>
                                                        <span class="badge bg-primary d-none d-md-inline">Total:
                                                            Bs. <?php echo e(number_format($total, 2)); ?></span>
                                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($venta->efectivo > 0): ?>
                                                            <span class="badge bg-success">
                                                                Bs. <?php echo e(number_format($venta->efectivo, 2)); ?></span>
                                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($venta->online > 0): ?>
                                                            <span class="badge bg-info">
                                                                Bs. <?php echo e(number_format($venta->online, 2)); ?></span>
                                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($venta->credito > 0): ?>
                                                            <span class="badge bg-danger">
                                                                Bs. <?php echo e(number_format($venta->credito, 2)); ?></span>
                                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                    </div>

                                                    <!-- Footer info -->
                                                    <div
                                                        class="d-flex justify-content-between align-items-center text-muted">
                                                        <small><i
                                                                class="fa-solid fa-user me-1"></i><?php echo e($venta->user->nombre ?? '—'); ?></small>
                                                        <small><i
                                                                class="fa-solid fa-calendar me-1"></i><?php echo e($venta->created_at->format('d/m/Y H:i')); ?></small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                <div class="col-12">
                                    <div class="text-center py-5 empty-state">
                                        <i class="fa-solid fa-shopping-cart fa-5x mb-3 text-muted"></i>
                                        <p class="h5 text-muted mb-0">No se encontraron ventas</p>
                                    </div>
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>

    <?php echo $__env->make('partials.paginate-bar', ['results' => $ventas, 'storageKey' => 'ventas'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <!-- Modal de Detalles de Venta -->
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($mostrarModal && $ventaSeleccionada): ?>
        <!-- Backdrop del Modal -->
        <div class="modal-backdrop fade show" style="z-index: 1040;"></div>

        <!-- Modal -->
        <div class="modal fade show d-block" tabindex="-1" role="dialog" aria-modal="true"
            style="z-index: 1050; overflow-y: auto;">
            <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                <div class="modal-content shadow-lg border-0">
                    <div class="modal-header text-white" style="background-color: var(--theme-default, #7366ff);">
                        <h5 class="modal-title mb-0">
                            <i class="fa-solid fa-shopping-cart me-2"></i>Venta #<?php echo e($ventaSeleccionada->numero_venta); ?>

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
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $ventaSeleccionada->ventaItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                        <tr>
                                            <td class="align-middle">
                                                <strong><?php echo e($item->producto->nombre ?? 'Producto'); ?></strong>
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(is_array($item->detalle)): ?>
                                                    <?php
                                                        $partes = [];
                                                        if(($item->detalle['arroz'] ?? 0) > 0) $partes[] = $item->detalle['arroz'].'A';
                                                        if(($item->detalle['fideo'] ?? 0) > 0) $partes[] = $item->detalle['fideo'].'F';
                                                        if(($item->detalle['mixto'] ?? 0) > 0) $partes[] = $item->detalle['mixto'].'M';
                                                    ?>
                                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($partes)): ?>
                                                        <small class="text-muted ms-1">(<?php echo e(implode(', ', $partes)); ?>)</small>
                                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            </td>
                                            <td class="text-end align-middle"><?php echo e($item->cantidad); ?></td>
                                            <td class="text-end align-middle">Bs. <?php echo e(number_format($item->precio, 2)); ?></td>
                                            <td class="text-end align-middle"><strong>Bs. <?php echo e(number_format($item->subtotal, 2)); ?></strong></td>
                                        </tr>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <td colspan="<?php echo e(3); ?>" class="text-end fw-bold">TOTAL</td>
                                        <td class="text-end fw-bold fs-6">Bs. <?php echo e(number_format($ventaSeleccionada->ventaItems->sum('subtotal'), 2)); ?></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <div class="modal-footer bg-light py-2">
                        <div class="d-flex align-items-center justify-content-between w-100 gap-3">
                            <div class="d-flex align-items-center gap-3 text-muted small">
                                <span><i class="fa-solid fa-user me-1"></i><strong><?php echo e($ventaSeleccionada->user->nombre ?? '—'); ?></strong></span>
                                <span><i class="fa-solid fa-calendar me-1"></i><?php echo e($ventaSeleccionada->fecha_hora ? $ventaSeleccionada->fecha_hora->format('d/m/Y H:i') : ($ventaSeleccionada->created_at ? $ventaSeleccionada->created_at->format('d/m/Y H:i') : '—')); ?></span>
                            </div>
                            <div class="d-flex gap-1">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($ventaSeleccionada->estado === 'Completo' && $puedeEliminar): ?>
                                    <button class="btn btn-sm btn-danger"
                                        wire:click="$dispatch('confirm-delete', { id: <?php echo e($ventaSeleccionada->id); ?>, message: '¿Está seguro de eliminar la venta #<?php echo e($ventaSeleccionada->numero_venta); ?>?' })"
                                        title="Eliminar">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <!-- Modal de Resumen de Eliminación -->
    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(false && $mostrarResumenEliminacion && !empty($resumenEliminacion)): ?>
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
                            Venta Eliminada #<?php echo e($resumenEliminacion['venta_id']); ?>

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
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $resumenEliminacion['productos']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $producto): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                        <tr>
                                            <td class="align-middle text-truncate">
                                                <strong><?php echo e($producto['nombre']); ?></strong>
                                            </td>
                                            <td class="text-end align-middle text-truncate">
                                                <span class="badge bg-warning text-dark">
                                                    <?php echo e($producto['stock_anterior_formateado']); ?>

                                                </span>
                                            </td>
                                            <td class="text-end align-middle text-truncate">
                                                <span class="badge bg-info text-dark">
                                                    <?php echo e($producto['cantidad_formateada']); ?>

                                                </span>
                                            </td>
                                            <td class="text-end align-middle text-truncate">
                                                <span class="badge bg-success">
                                                    <?php echo e($producto['stock_nuevo_formateado']); ?>

                                                </span>
                                            </td>
                                        </tr>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="modal-footer bg-light">
                        <div class="row g-2 justify-content-center w-100">
                            <div class="col-6 col-md-4">
                                <div class="rounded px-3 py-2 text-center h-100 d-flex flex-column justify-content-center" style="background-color: #f0f0f0;">
                                    <small class="text-dark d-block">Retirado de Caja</small>
                                    <span class="fw-bold fs-5 text-danger">Bs. <?php echo e(number_format($resumenEliminacion['devuelto_caja'] ?? 0, 2)); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php
        $__scriptKey = '269646266-0';
        ob_start();
    ?>
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

            // === IMPRESIÓN DIRECTA VÍA LICOPOS PRINTER (localhost:1013) ===
            const LICOPOS_URL = 'http://localhost:1013';

            async function imprimirTicketLocal(ventaId) {
                try {
                    const response = await fetch(`${LICOPOS_URL}/venta/${ventaId}`, {
                        method: 'GET',
                    });

                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }

                    // Consumir respuesta
                    await response.text();
                    console.log('Ticket impreso correctamente por localhost:1013');

                    // Mostrar notificación de éxito
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: 'Ticket impreso',
                            showConfirmButton: false,
                            timer: 2000
                        });
                    }
                    return true; // Éxito - no abrir PDF
                } catch (e) {
                    console.warn('MiSocio Printer no disponible, generando PDF:', e.message);
                    // Fallback: Generar PDF y abrir para imprimir (compatible con móviles)
                    const printWindow = window.open(`/ticket/venta/${ventaId}`, '_blank');
                    if (printWindow) {
                        // Intentar imprimir automáticamente cuando el PDF cargue
                        printWindow.onload = function() {
                            setTimeout(() => {
                                try {
                                    printWindow.print();
                                } catch (err) {
                                    console.warn('No se pudo imprimir automáticamente:', err);
                                }
                            }, 500);
                        };
                    }
                }
            }

            // Escuchar evento de Livewire para imprimir
            $wire.on('abrir-ticket', (data) => {
                const info = data[0] || data;
                imprimirTicketLocal(info.ventaId);
            });
        </script>
        <?php
        $__output = ob_get_clean();

        \Livewire\store($this)->push('scripts', $__output, $__scriptKey)
    ?>

    <!-- Modal de Filtro de Fechas - Overlay Completo -->
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($mostrarModalFiltro): ?>
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
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php
        $__scriptKey = '269646266-1';
        ob_start();
    ?>
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
        <?php
        $__output = ob_get_clean();

        \Livewire\store($this)->push('scripts', $__output, $__scriptKey)
    ?>

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
    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(false && $mostrarModalPago && $ventaAPagar): ?>
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(255,255,255,0.95); overflow-y: auto;"
            x-data="{
                efectivo: <?php echo e($montoPagoEfectivo ?? 0); ?>,
                online: <?php echo e($montoPagoOnline ?? 0); ?>,
                creditoTotal: <?php echo e($ventaAPagar->credito); ?>,
                get totalPago() {
                    return parseFloat(this.efectivo || 0) + parseFloat(this.online || 0);
                },
                get creditoRestante() {
                    return Math.max(0, this.creditoTotal - this.totalPago);
                },
                finalizarPago() {
                    if (<?php echo e($procesandoPago ? 'true' : 'false'); ?> || this.totalPago <= 0 || this.totalPago > this.creditoTotal) {
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
                        <button type="button" class="btn-close btn-close-white" wire:click="cerrarModalPago" <?php echo e($procesandoPago ? 'disabled' : ''); ?>></button>
                    </div>
                    <div class="modal-body">
                        <!-- Información de la Venta -->
                        <div class="row g-2 mb-4">
                            <div class="col-6">
                                <div class="p-2 bg-light rounded text-center">
                                    <small class="text-muted d-block">Venta:</small>
                                    <strong class="d-block text-dark">#<?php echo e($ventaAPagar->numero_venta); ?></strong>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-2 bg-light rounded text-center">
                                    <small class="text-muted d-block">Cliente:</small>
                                    <strong class="d-block text-truncate text-dark px-2" title="<?php echo e($ventaAPagar->cliente->nombre ?? 'Sin cliente'); ?>"><?php echo e($ventaAPagar->cliente->nombre ?? 'Sin cliente'); ?></strong>
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
                                        <?php echo e($procesandoPago ? 'disabled' : ''); ?>>
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
                                        <?php echo e($procesandoPago ? 'disabled' : ''); ?>>
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
                            <?php echo e($procesandoPago ? 'disabled' : ''); ?>>
                            <i class="fa-solid fa-times me-1"></i>
                            Cancelar
                        </button>
                        <button type="button"
                            class="btn btn-success"
                            @click="finalizarPago()"
                            :disabled="<?php echo e($procesandoPago ? 'true' : 'false'); ?> || totalPago <= 0 || totalPago > creditoTotal">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($procesandoPago): ?>
                                <span class="spinner-border spinner-border-sm me-1"></span>
                                Procesando...
                            <?php else: ?>
                                <i class="fa-solid fa-check me-1"></i>
                                Finalizar Pago <span class="badge bg-white text-success ms-1">Enter</span>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(false && $procesandoPago && $mostrarModalPago): ?>
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
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    
    
    
</div>
<?php /**PATH C:\laragon\www\tpv\resources\views/livewire/ventas.blade.php ENDPATH**/ ?>