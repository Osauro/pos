<div x-data x-on:actualizar-badge-venta.window="$wire.actualizarContador()">
    @if($ventaPendienteId)
        <a href="{{ route('venta', ['ventaId' => $ventaPendienteId]) }}" class="cart-icon-link position-relative" title="Venta en proceso">
    @else
        <a href="{{ route('ventas') }}" class="cart-icon-link position-relative" title="Ventas">
    @endif
        <i class="fa-solid fa-shopping-cart fa-lg"></i>
        @if($cantidadPendientes > 0)
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                {{ $cantidadPendientes }}
                <span class="visually-hidden">ventas pendientes</span>
            </span>
        @endif
    </a>
</div>
