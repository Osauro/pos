<aside class="page-sidebar">
    <div class="left-arrow" id="left-arrow"><i data-feather="arrow-left"></i></div>
    <div class="main-sidebar" id="main-sidebar">
        @php
            // Verificar turno de la semana vigente
            $inicioSemana = now()->startOfWeek(\Carbon\Carbon::MONDAY)->toDateString();
            $finSemana    = now()->startOfWeek(\Carbon\Carbon::MONDAY)->addDays(6)->toDateString();
            $turnoActivo  = \App\Models\Turno::withoutGlobalScopes()
                ->where('encargado_id', auth()->id())
                ->where('tenant_id', currentTenantId())
                ->where('fecha_inicio', '<=', $finSemana)
                ->where('fecha_fin', '>=', $inicioSemana)
                ->exists();

            // Permisos por módulo
            $esAdmin          = canManageTenant();
            $turnoSemana      = \App\Models\Turno::where('tenant_id', currentTenantId())
                                    ->where('fecha_inicio', '<=', $finSemana)
                                    ->where('fecha_fin', '>=', $inicioSemana)
                                    ->exists();
            $puedeGestionar   = $esAdmin;                   // Admin siempre puede gestionar usuarios/productos
            $puedePOS         = $esAdmin ? $turnoActivo : $turnoSemana; // Operador: necesita turno abierto
            $puedeDashboard   = $esAdmin;            // Solo admin ve el dashboard
            $puedeTurnos      = $esAdmin;                   // Siempre visible para admin
            $puedeMovimientos = true;
            $puedeVentas      = true;
        @endphp
        <ul class="sidebar-menu" id="simple-bar">
            <li class="pin-title sidebar-main-title">
                <div>
                    <h5 class="sidebar-title f-w-700">Menú Principal</h5>
                </div>
            </li>

            @if($puedeDashboard)
            <!-- Dashboard -->
            <li class="sidebar-list">
                <a class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                    <i class="fa-solid fa-house-chimney fa-fw" style="font-size: 20px; color: var(--theme-default);"></i>
                    <h6 class="f-w-600">Inicio</h6>
                </a>
            </li>
            @endif

            @if($puedePOS)
            <!-- Punto de Venta -->
            <li class="sidebar-list">
                <a class="sidebar-link" href="{{ route('pos') }}">
                    <i class="fa-solid fa-cash-register fa-fw" style="font-size: 20px; color: var(--theme-default);"></i>
                    <h6 class="f-w-600">Punto de Venta</h6>
                </a>
            </li>
            @endif

            @if($puedeGestionar)
            <!-- Usuarios -->
            <li class="sidebar-list">
                <a class="sidebar-link" href="{{ route('usuarios') }}">
                    <i class="fa-solid fa-users fa-fw" style="font-size: 20px; color: var(--theme-default);"></i>
                    <h6 class="f-w-600">Usuarios</h6>
                </a>
            </li>
            @endif

            @if($puedeGestionar)
            <!-- Productos -->
            <li class="sidebar-list">
                <a class="sidebar-link" href="{{ route('productos') }}">
                    <i class="fa-solid fa-box fa-fw" style="font-size: 20px; color: var(--theme-default);"></i>
                    <h6 class="f-w-600">Productos</h6>
                </a>
            </li>
            @endif

            @if($puedeTurnos)
            <!-- Turnos -->
            <li class="sidebar-list">
                <a class="sidebar-link" href="{{ route('turnos') }}">
                    <i class="fa-solid fa-calendar-days fa-fw" style="font-size: 20px; color: var(--theme-default);"></i>
                    <h6 class="f-w-600">Turnos</h6>
                </a>
            </li>
            @endif

            @if($puedeMovimientos)
            <!-- Movimientos -->
            <li class="sidebar-list">
                <a class="sidebar-link" href="{{ route('movimientos') }}">
                    <i class="fa-solid fa-file-invoice-dollar fa-fw" style="font-size: 20px; color: var(--theme-default);"></i>
                    <h6 class="f-w-600">Movimientos</h6>
                </a>
            </li>
            @endif

            @if($puedeVentas)
            <!-- Ventas -->
            <li class="sidebar-list">
                <a class="sidebar-link" href="{{ route('ventas') }}">
                    <i class="fa-solid fa-shopping-cart fa-fw" style="font-size: 20px; color: var(--theme-default);"></i>
                    <h6 class="f-w-600">Ventas</h6>
                </a>
            </li>
            @endif

            @if($puedeGestionar)
            <!-- Configuración -->
            <li class="sidebar-list">
                <a class="sidebar-link {{ request()->routeIs('configuracion.impresora') ? 'active' : '' }}" href="{{ route('configuracion.impresora') }}">
                    <i class="fa-solid fa-gear fa-fw" style="font-size: 20px; color: var(--theme-default);"></i>
                    <h6 class="f-w-600">Configuración</h6>
                </a>
            </li>
            @endif

            @if($puedeGestionar)
            <!-- Suscripción -->
            <li class="sidebar-list">
                <a class="sidebar-link {{ request()->routeIs('suscripcion') ? 'active' : '' }}" href="{{ route('suscripcion') }}">
                    <i class="fa-solid fa-crown fa-fw" style="font-size: 20px; color: var(--theme-default);"></i>
                    <h6 class="f-w-600">Suscripción</h6>
                </a>
            </li>
            @endif
        </ul>
    </div>
    <div class="right-arrow" id="right-arrow"><i data-feather="arrow-right"></i></div>

    <!-- Parte inferior del sidebar -->
    <div style="position: fixed; bottom: 0; width: 265px; background: #fff; border-top: 1px solid #e6edef; z-index: 1052;">
        @php $esLandlordSb = isLandlord(); @endphp
        {{-- Botones: Cambiar Tienda + Modo Landlord --}}
        <div style="padding: 8px 16px; display: flex; gap: 8px; border-bottom: 1px solid #f0f0f0;">
            <button type="button" onclick="abrirTiendaOverlay()"
                style="flex:1; background:var(--theme-default); color:#fff; border:none; border-radius:8px; padding:7px 4px; display:flex; align-items:center; justify-content:center; gap:6px; font-size:0.8rem; font-weight:600; cursor:pointer;">
                <i class="fa-solid fa-store"></i> Tiendas
            </button>
            @if($esLandlordSb)
            <a href="{{ route('admin.dashboard') }}"
                style="flex:1; background:var(--theme-default); color:#fff; border-radius:8px; padding:7px 4px; display:flex; align-items:center; justify-content:center; gap:6px; font-size:0.8rem; font-weight:600; text-decoration:none;">
                <i class="fa-solid fa-crown"></i> Landlord
            </a>
            @endif
        </div>
        {{-- Usuario y logout --}}
        <div style="padding: 12px 16px; display: flex; align-items: center; justify-content: space-between; gap: 8px;">
            <div class="d-flex align-items-center gap-2 overflow-hidden">
                <i class="fa-solid fa-circle-user" style="font-size: 22px; color: var(--theme-default); flex-shrink: 0;"></i>
                <span class="text-truncate fw-semibold" style="font-size: 0.85rem; color: #333;">{{ auth()->user()->nombre ?? auth()->user()->name }}</span>
            </div>
            <form action="{{ route('logout') }}" method="POST" class="mb-0">
                @csrf
                <button type="submit" class="btn btn-sm" style="background: var(--theme-default); color: white; padding: 4px 8px; flex-shrink: 0;" title="Cerrar sesión">
                    <i class="fa-solid fa-right-from-bracket"></i>
                </button>
            </form>
        </div>
    </div>
</aside>
