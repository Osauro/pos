<div>
    
    <div class="module-sticky-header">
        <div class="d-flex justify-content-between align-items-center gap-2">
            <h5 class="mb-0 fw-bold d-none d-md-block flex-shrink-0">
                <i class="fa-solid fa-cash-register me-2 text-primary"></i>Punto de Venta
            </h5>
            <div class="btn-group pos-cat-group" role="group">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = [['Platos','fa-utensils'], ['Refrescos','fa-glass-water'], ['Porciones','fa-bowl-food']]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$cat, $ico]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                    <button type="button"
                            wire:click="setTipoFiltro('<?php echo e($cat); ?>')"
                            class="btn <?php echo e($tipo_filtro === $cat ? 'btn-primary' : 'btn-outline-primary'); ?>">
                        <i class="fa-solid <?php echo e($ico); ?> me-1"></i><?php echo e($cat); ?>

                    </button>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </div>
        </div>
    </div>

    <style>
        .bottom-nav        { display: none !important; }
        .page-body         { padding: 0 !important; overflow: hidden !important; }
        .page-body-wrapper { overflow: hidden !important; }
        html, body         { overflow: hidden !important; height: 100% !important; }
        @media (max-width: 767.98px) {
            .pos-cat-group { width: 100%; }
            .pos-cat-group .btn { flex: 1 1 auto; }
        }
    </style>

    
    <div class="row g-0 pos-layout">

        
        <div class="col-12 col-md-9 pos-catalog <?php echo e($mostrar_carrito ? 'd-none d-md-block' : ''); ?>">

            
            <div class="pos-products-wrap p-2 bg-light">
                <div class="row g-2">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $productos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $producto): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                        <?php
                            $ico = match($producto->tipo) {
                                'Refrescos' => 'fa-glass-water',
                                'Porciones' => 'fa-bowl-food',
                                default     => 'fa-utensils'
                            };
                            $cantidadEnCarrito = collect($carrito)
                                ->where('producto_id', $producto->id)
                                ->sum('cantidad');
                            $enCarrito = $cantidadEnCarrito > 0;
                        ?>
                        <div class="col-6 col-md-4 col-lg-3 col-xl-2">
                            <div class="card prod-card h-100 <?php echo e($enCarrito ? 'prod-card--active' : ''); ?>"
                                 wire:click="agregarAlCarrito(<?php echo e($producto->id); ?>)"
                                 wire:loading.class="opacity-50"
                                 wire:target="agregarAlCarrito(<?php echo e($producto->id); ?>)">

                                <div class="prod-card__img-wrap">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($producto->imagen): ?>
                                    <img src="<?php echo e(asset('storage/' . $producto->imagen)); ?>"
                                         class="prod-card__img"
                                         alt="<?php echo e($producto->nombre); ?>">
                                <?php else: ?>
                                    <div class="prod-card__placeholder d-flex align-items-center justify-content-center">
                                        <i class="fa-solid <?php echo e($ico); ?> fa-2x text-primary opacity-50"></i>
                                    </div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <span class="prod-card__precio-overlay">Bs. <?php echo e(number_format($producto->precio, 2)); ?></span>
                                </div>

                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($enCarrito): ?>
                                    <span class="prod-card__badge"><?php echo e($cantidadEnCarrito); ?></span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                                <div class="card-body p-2 text-center">
                                    <p class="prod-card__nombre mb-0"><?php echo e($producto->nombre); ?></p>
                                </div>
                            </div>
                        </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <div class="col-12 text-center text-muted py-5">
                            <i class="fa-solid fa-plate-wheat fa-3x d-block mb-3 opacity-25"></i>
                            <p class="mb-0">Sin productos disponibles en esta categoría</p>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        </div>

        
        <div class="col-12 col-md-3 pos-cart border-start <?php echo e(!$mostrar_carrito ? 'd-none d-md-block' : ''); ?>">

            
            <div class="pos-cart-header px-3 py-2 bg-white border-bottom">
                <div class="d-flex align-items-center justify-content-between">
                    <span class="fw-bold">
                        <i class="fa-solid fa-cart-shopping text-primary me-1"></i> Pedido
                    </span>
                    <?php
                        $totalPlatos    = collect($carrito)->where('categoria','Platos')->sum('cantidad');
                        $totalRefrescos = collect($carrito)->where('categoria','Refrescos')->sum('cantidad');
                        $totalPorciones = collect($carrito)->where('categoria','Porciones')->sum('cantidad');
                    ?>
                    <div class="d-flex align-items-center gap-2">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($totalPlatos > 0): ?>
                            <span class="badge rounded-pill px-3 py-2" style="background:#7c4b2a;font-size:.82rem">
                                <i class="fa-solid fa-utensils me-1"></i><?php echo e($totalPlatos); ?>

                            </span>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($totalRefrescos > 0): ?>
                            <span class="badge rounded-pill px-3 py-2 bg-info text-white" style="font-size:.82rem">
                                <i class="fa-solid fa-glass-water me-1"></i><?php echo e($totalRefrescos); ?>

                            </span>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($totalPorciones > 0): ?>
                            <span class="badge rounded-pill px-3 py-2 bg-success" style="font-size:.82rem">
                                <i class="fa-solid fa-bowl-food me-1"></i><?php echo e($totalPorciones); ?>

                            </span>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>
            </div>

            
            <div class="pos-cart-body p-2 bg-light">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $carrito; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                    <div class="card border-0 shadow-sm mb-2">
                        <div class="card-body p-2">
                            <div class="d-flex align-items-stretch gap-2">

                                
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($item['imagen']): ?>
                                    <img src="<?php echo e(asset('storage/' . $item['imagen'])); ?>"
                                         class="rounded flex-shrink-0 align-self-center"
                                         style="width:56px;height:56px;object-fit:cover">
                                <?php else: ?>
                                    <div class="rounded flex-shrink-0 align-self-center bg-light border d-flex align-items-center justify-content-center"
                                         style="width:56px;height:56px">
                                        <i class="fa-solid fa-utensils text-muted"></i>
                                    </div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                                
                                <div class="flex-fill d-flex flex-column justify-content-center" style="min-width:0;gap:3px">

                                    
                                    <div class="d-flex align-items-center justify-content-between gap-1 w-100">
                                        <div class="d-flex align-items-center gap-1 flex-fill" style="min-width:0">
                                            <span class="fw-bold flex-shrink-0" style="font-size:.85rem"><?php echo e($item['cantidad']); ?></span>
                                            <span class="text-muted flex-shrink-0" style="font-size:.85rem">-</span>
                                            <span class="text-truncate" style="font-size:.85rem"><?php echo e($item['nombre']); ?></span>
                                        </div>
                                        <button wire:click="eliminarDelCarrito('<?php echo e($key); ?>')"
                                                class="btn btn-outline-danger btn-sm flex-shrink-0 py-0 px-1" style="line-height:1.4">
                                            <i class="fa-solid fa-xmark" style="font-size:.7rem"></i>
                                        </button>
                                    </div>

                                    
                                    <div class="d-flex align-items-center justify-content-between gap-1 w-100">
                                        <div class="d-flex align-items-center gap-2">
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($item['tipo'] === 'plato'): ?>
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ['arroz' => 'A', 'fideo' => 'F', 'mixto' => 'M']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $det => $etiqueta): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($item['acomp'][$det] ?? 0) > 0): ?>
                                                    <div class="d-flex align-items-center gap-1 flex-shrink-0">
                                                        <span class="text-muted" style="font-size:.75rem"><?php echo e($etiqueta); ?>:</span>
                                                        <input type="number" min="0"
                                                               class="form-control form-control-sm text-center px-0"
                                                               style="width:36px;height:22px;font-size:.78rem"
                                                               value="<?php echo e($item['acomp'][$det]); ?>"
                                                               x-on:focus="$event.target.select()"
                                                               x-on:change="$wire.actualizarAcompanamiento('<?php echo e($key); ?>', '<?php echo e($det); ?>', $event.target.value)">
                                                    </div>
                                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </div>
                                        <span class="fw-bold text-success flex-shrink-0" style="font-size:.82rem">Bs. <?php echo e(number_format($item['subtotal'], 2)); ?></span>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <div style="position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;" class="text-muted">
                        <div class="rounded-circle d-flex align-items-center justify-content-center mb-3"
                             style="width:72px;height:72px;background:rgba(115,102,255,.08)">
                            <i class="fa-solid fa-cart-shopping fa-2x" style="color:rgba(115,102,255,.35)"></i>
                        </div>
                        <p class="fw-semibold mb-1" style="font-size:.9rem;color:#6c757d">Pedido vacío</p>
                        <p class="mb-0" style="font-size:.78rem;color:#adb5bd">Toca un producto para añadirlo</p>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            
            <div class="pos-cart-footer">
                <div class="px-3 py-3 d-flex gap-2">
                    <button wire:click="cancelarVenta"
                            class="btn btn-danger btn-lg px-3"
                            title="Cancelar venta"
                            <?php echo e(empty($carrito) ? 'disabled' : ''); ?>>
                        <i class="fa-solid fa-ban"></i>
                    </button>
                    <button wire:click="procesarVenta"
                            wire:loading.attr="disabled"
                            wire:target="procesarVenta"
                            class="btn btn-success btn-lg fw-bold flex-fill"
                            <?php echo e(empty($carrito) ? 'disabled' : ''); ?>>
                        <i class="fa-solid fa-check me-2"></i>Bs. <?php echo e(number_format($total, 2)); ?>

                    </button>
                </div>
            </div>
        </div>

    </div>

    
    <div class="pos-bottom-bar d-md-none">
        <button wire:click="cancelarVenta"
                class="pos-act pos-act--danger"
                <?php echo e(empty($carrito) ? 'disabled' : ''); ?>>
            <i class="fa-solid fa-ban"></i>
            <span>Cancelar</span>
        </button>
        <button wire:click="toggleCarrito" class="pos-act pos-act--cart">
            <i class="fa-solid fa-<?php echo e($mostrar_carrito ? 'utensils' : 'cart-shopping'); ?>"></i>
            <span><?php echo e($mostrar_carrito ? 'Menú' : 'Carrito'); ?></span>
            <?php $totalItems = collect($carrito)->sum('cantidad'); ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($totalItems > 0): ?>
                <span class="pos-act__badge"><?php echo e($totalItems); ?></span>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </button>
        <button wire:click="procesarVenta"
                wire:loading.attr="disabled"
                wire:target="procesarVenta"
                class="pos-act pos-act--pay"
                <?php echo e(empty($carrito) ? 'disabled' : ''); ?>>
            <i class="fa-solid fa-check"></i>
            <span>Bs. <?php echo e(number_format($total, 2)); ?></span>
        </button>
    </div>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($mostrar_selector): ?>
        <?php $prodPendiente = $productos->firstWhere('id', $producto_pendiente_id); ?>
        <div class="pos-selector-overlay" wire:click="cancelarSelector">
            <div class="pos-selector" wire:click.stop>
                <div class="pos-selector__handle"></div>
                <div class="pos-selector__nombre"><?php echo e($prodPendiente->nombre ?? 'Producto'); ?></div>
                <p class="pos-selector__sub">¿Con qué acompañamiento?</p>
                <div class="pos-selector__opciones">
                    <button wire:click="seleccionarAcompanamiento('arroz')" class="pos-sel-btn">
                        <span class="pos-sel-btn__icon">🍚</span>
                        <span class="pos-sel-btn__label">Arroz</span>
                    </button>
                    <button wire:click="seleccionarAcompanamiento('fideo')" class="pos-sel-btn">
                        <span class="pos-sel-btn__icon">🍝</span>
                        <span class="pos-sel-btn__label">Fideo</span>
                    </button>
                    <button wire:click="seleccionarAcompanamiento('mixto')" class="pos-sel-btn">
                        <span class="pos-sel-btn__icon">🍱</span>
                        <span class="pos-sel-btn__label">Mixto</span>
                    </button>
                </div>
                <button wire:click="cancelarSelector" class="pos-selector__cancel">Cancelar</button>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($mostrar_modal_caja): ?>
        <div class="pos-selector-overlay">
            <div class="pos-selector" wire:click.stop>
                <div class="pos-selector__handle"></div>
                <div class="pos-selector__nombre">
                    <i class="fa-solid fa-cash-register me-2 text-warning"></i>Iniciar Caja
                </div>
                <p class="pos-selector__sub">Primer movimiento del día. Ingresa el monto de cambio inicial.</p>
                <div class="px-3 pb-3 w-100">
                    <input type="number"
                           wire:model="monto_caja"
                           class="form-control form-control-lg text-center"
                           placeholder="0.00"
                           min="0.01"
                           step="0.01"
                           autofocus
                           wire:keydown.enter="confirmarInicioCaja">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['monto_caja'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <div class="text-danger small mt-1 text-center"><?php echo e($message); ?></div>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                <div class="pos-selector__opciones" style="grid-template-columns: 1fr;">
                    <button wire:click="confirmarInicioCaja" class="pos-sel-btn" style="background:#198754;color:#fff;border-color:#198754;">
                        <span class="pos-sel-btn__icon">💵</span>
                        <span class="pos-sel-btn__label">Confirmar</span>
                    </button>
                </div>
                <button wire:click="cancelarInicioCaja" class="pos-selector__cancel">Cancelar</button>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

</div>

    <?php
        $__scriptKey = '3274969947-0';
        ob_start();
    ?>
<script>
    // 300px = exactamente 80mm a 96dpi
    const WIN_OPTS = 'toolbar=0,scrollbars=0,location=0,statusbar=0,menubar=0,resizable=0,width=300,height=700';

    $wire.on('imprimir-venta', (data) => {
        const ventaId = (data[0] || data).ventaId;
        if (!ventaId) return;
        // Ticket + Comanda en una sola ventana: imprime dos hojas y cierra automáticamente
        window.open(`/ticket/cliente/${ventaId}`, '_blank', WIN_OPTS);
    });
</script>
    <?php
        $__output = ob_get_clean();

        \Livewire\store($this)->push('scripts', $__output, $__scriptKey)
    ?>
<?php /**PATH C:\laragon\www\tpv\resources\views/livewire/pos.blade.php ENDPATH**/ ?>