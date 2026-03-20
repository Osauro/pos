<aside class="page-sidebar">
    <div class="left-arrow" id="left-arrow"><i data-feather="arrow-left"></i></div>
    <div class="main-sidebar" id="main-sidebar">
        <?php
            $userId       = auth()->id();
            $userTipo     = auth()->user()->tipo;
            $esSuperAdmin = $userId === 1;
            $esAdmin      = $userTipo === 'admin';
            $esUser       = $userTipo === 'user';

            // Verificar turno de la semana vigente (actual)
            $inicioSemana = now()->startOfWeek(\Carbon\Carbon::MONDAY)->toDateString();
            $finSemana    = now()->startOfWeek(\Carbon\Carbon::MONDAY)->addDays(6)->toDateString();
            $turnoActivo  = \App\Models\Turno::where('encargado_id', $userId)
                ->where('fecha_inicio', '<=', $finSemana)
                ->where('fecha_fin', '>=', $inicioSemana)
                ->exists();

            // Permisos por módulo
            $puedeUsuarios    = $esSuperAdmin || ($esAdmin && $turnoActivo);
            $puedeProductos   = $esSuperAdmin || ($esAdmin && $turnoActivo);
            $puedeTurnos      = $esSuperAdmin || $esAdmin;
            $puedePOS         = $esSuperAdmin || ($esAdmin && $turnoActivo) || $esUser;
            $puedeMovimientos = $esSuperAdmin || $esAdmin || $esUser;
            $puedeVentas      = $esSuperAdmin || $esAdmin || $esUser;
        ?>
        <ul class="sidebar-menu" id="simple-bar">
            <li class="pin-title sidebar-main-title">
                <div>
                    <h5 class="sidebar-title f-w-700">Menú Principal</h5>
                </div>
            </li>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($puedePOS): ?>
            <!-- Punto de Venta -->
            <li class="sidebar-list">
                <a class="sidebar-link" href="<?php echo e(route('pos')); ?>">
                    <i class="fa-solid fa-cash-register fa-fw" style="font-size: 20px; color: var(--theme-default);"></i>
                    <h6 class="f-w-600">Punto de Venta</h6>
                </a>
            </li>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($puedeUsuarios): ?>
            <!-- Usuarios -->
            <li class="sidebar-list">
                <a class="sidebar-link" href="<?php echo e(route('usuarios')); ?>">
                    <i class="fa-solid fa-users fa-fw" style="font-size: 20px; color: var(--theme-default);"></i>
                    <h6 class="f-w-600">Usuarios</h6>
                </a>
            </li>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($puedeProductos): ?>
            <!-- Productos -->
            <li class="sidebar-list">
                <a class="sidebar-link" href="<?php echo e(route('productos')); ?>">
                    <i class="fa-solid fa-box fa-fw" style="font-size: 20px; color: var(--theme-default);"></i>
                    <h6 class="f-w-600">Productos</h6>
                </a>
            </li>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($puedeTurnos): ?>
            <!-- Turnos -->
            <li class="sidebar-list">
                <a class="sidebar-link" href="<?php echo e(route('turnos')); ?>">
                    <i class="fa-solid fa-calendar-days fa-fw" style="font-size: 20px; color: var(--theme-default);"></i>
                    <h6 class="f-w-600">Turnos</h6>
                </a>
            </li>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($puedeMovimientos): ?>
            <!-- Movimientos -->
            <li class="sidebar-list">
                <a class="sidebar-link" href="<?php echo e(route('movimientos')); ?>">
                    <i class="fa-solid fa-file-invoice-dollar fa-fw" style="font-size: 20px; color: var(--theme-default);"></i>
                    <h6 class="f-w-600">Movimientos</h6>
                </a>
            </li>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($puedeVentas): ?>
            <!-- Ventas -->
            <li class="sidebar-list">
                <a class="sidebar-link" href="<?php echo e(route('ventas')); ?>">
                    <i class="fa-solid fa-shopping-cart fa-fw" style="font-size: 20px; color: var(--theme-default);"></i>
                    <h6 class="f-w-600">Ventas</h6>
                </a>
            </li>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </ul>
    </div>
    <div class="right-arrow" id="right-arrow"><i data-feather="arrow-right"></i></div>

    <!-- Usuario y logout fijo en la parte inferior del sidebar -->
    <div style="position: fixed; bottom: 0; width: 265px; background: #fff; border-top: 1px solid #e6edef; padding: 12px 16px; z-index: 1052; display: flex; align-items: center; justify-content: space-between; gap: 8px;">
        <div class="d-flex align-items-center gap-2 overflow-hidden">
            <i class="fa-solid fa-circle-user" style="font-size: 22px; color: var(--theme-default); flex-shrink: 0;"></i>
            <span class="text-truncate fw-semibold" style="font-size: 0.85rem; color: #333;"><?php echo e(auth()->user()->nombre ?? auth()->user()->name); ?></span>
        </div>
        <form action="<?php echo e(route('logout')); ?>" method="POST" class="mb-0">
            <?php echo csrf_field(); ?>
            <button type="submit" class="btn btn-sm" style="background: var(--theme-default); color: white; padding: 4px 8px; flex-shrink: 0;" title="Cerrar sesión">
                <i class="fa-solid fa-right-from-bracket"></i>
            </button>
        </form>
    </div>
</aside>
<?php /**PATH C:\laragon\www\tpv\resources\views/layouts/theme/sidebar.blade.php ENDPATH**/ ?>