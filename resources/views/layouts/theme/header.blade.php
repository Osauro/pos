<header class="page-header row">
    <div class="logo-wrapper d-flex align-items-center col-auto p-0">
        <a href="{{ route('pos') }}">
            <img class="light-logo img-fluid" src="{{ asset('assets/images/logo.png') }}" alt="TPV Logo"
                style="height: 60px!important; margin-left:10px" />
        </a>
        <a class="close-btn toggle-sidebar" href="javascript:void(0)">
            <i class="fa-solid fa-bars fa-lg"></i>
        </a>
    </div>
    <div class="page-main-header col d-flex justify-content-end align-items-center">
        <div class="d-flex align-items-center gap-2 me-2">
            <a href="{{ route('pos') }}" title="POS"
               style="background:var(--theme-default);color:#fff;border-radius:50%;width:40px;height:40px;display:flex;align-items:center;justify-content:center;flex-shrink:0;text-decoration:none;">
                <i class="fa-solid fa-cash-register" style="font-size:16px;color:#fff!important"></i>
            </a>
            <a href="{{ route('movimientos') }}" title="Movimientos"
               style="background:var(--theme-default);color:#fff;border-radius:50%;width:40px;height:40px;display:flex;align-items:center;justify-content:center;flex-shrink:0;text-decoration:none;">
                <i class="fa-solid fa-file-invoice-dollar" style="font-size:16px;color:#fff!important"></i>
            </a>
            <a href="{{ route('ventas') }}" title="Ventas"
               style="background:var(--theme-default);color:#fff;border-radius:50%;width:40px;height:40px;display:flex;align-items:center;justify-content:center;flex-shrink:0;text-decoration:none;">
                <i class="fa-solid fa-receipt" style="font-size:16px;color:#fff!important"></i>
            </a>
        </div>
    </div>
</header>
