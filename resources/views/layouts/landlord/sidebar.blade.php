<aside class="page-sidebar">
    <div class="left-arrow" id="left-arrow"><i data-feather="arrow-left"></i></div>
    <div class="main-sidebar" id="main-sidebar">
        <ul class="sidebar-menu" id="simple-bar">
            <li class="pin-title sidebar-main-title">
                <div>
                    <h5 class="sidebar-title f-w-700">Panel Landlord</h5>
                </div>
            </li>

            <!-- Dashboard -->
            <li class="sidebar-list">
                <i class="fa-solid fa-thumbtack"></i>
                <a class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
                   href="{{ route('admin.dashboard') }}">
                    <i class="fa-solid fa-chart-line fa-fw" style="font-size: 20px; color: var(--theme-default);"></i>
                    <h6 class="f-w-600">Dashboard</h6>
                </a>
            </li>

            <!-- Negocios -->
            <li class="sidebar-list">
                <i class="fa-solid fa-thumbtack"></i>
                <a class="sidebar-link {{ request()->routeIs('admin.tenants') ? 'active' : '' }}"
                   href="{{ route('admin.tenants') }}">
                    <i class="fa-solid fa-store fa-fw" style="font-size: 20px; color: var(--theme-default);"></i>
                    <h6 class="f-w-600">Negocios</h6>
                </a>
            </li>

            <!-- Pagos -->
            <li class="sidebar-list">
                <i class="fa-solid fa-thumbtack"></i>
                <a class="sidebar-link {{ request()->routeIs('admin.pagos') ? 'active' : '' }}"
                   href="{{ route('admin.pagos') }}">
                    <i class="fa-solid fa-money-bill-wave fa-fw" style="font-size: 20px; color: var(--theme-default);"></i>
                    <h6 class="f-w-600">
                        Pagos
                        @php
                            $pagosPendientesCount = \App\Models\PagoSuscripcion::where('estado','pendiente')->count();
                        @endphp
                        @if($pagosPendientesCount > 0)
                            <span class="badge bg-danger ms-1" style="font-size:0.6rem;">{{ $pagosPendientesCount }}</span>
                        @endif
                    </h6>
                </a>
            </li>

        </ul>
    </div>
    <div class="right-arrow" id="right-arrow"><i data-feather="arrow-right"></i></div>

    <!-- Parte inferior del sidebar -->
    <div style="position: fixed; bottom: 0; width: 265px; background: #fff; border-top: 1px solid #e6edef; z-index: 1052;">
        {{-- Botón: Cambiar Tienda --}}
        <div style="padding: 8px 16px; border-bottom: 1px solid #f0f0f0;">
            <button type="button" onclick="abrirTiendaOverlay()"
                style="width:100%; background:#7366ff; color:#fff; border:none; border-radius:8px; padding:7px 4px; display:flex; align-items:center; justify-content:center; gap:6px; font-size:0.8rem; font-weight:600; cursor:pointer;">
                <i class="fa-solid fa-store"></i> Cambiar Tienda
            </button>
        </div>
        {{-- Usuario y logout --}}
        <div style="padding: 12px 16px; display: flex; align-items: center; justify-content: space-between; gap: 8px;">
            <div class="d-flex align-items-center gap-2 overflow-hidden">
                <i class="fa-solid fa-shield-halved" style="font-size: 22px; color: var(--theme-default); flex-shrink: 0;"></i>
                <div class="overflow-hidden">
                    <div class="text-truncate fw-semibold" style="font-size: 0.85rem; color: #333;">
                        {{ auth()->user()->nombre ?? 'Admin' }}
                    </div>
                    <div style="font-size: 0.7rem; color: #999;">Landlord</div>
                </div>
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
