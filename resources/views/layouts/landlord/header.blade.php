<header class="page-header row">
    <div class="logo-wrapper d-flex align-items-center col-auto p-0">
        <a href="{{ route('admin.tenants') }}">
            <img class="light-logo img-fluid" src="{{ asset('assets/images/logo.png') }}" alt="TPV"
                style="height: 60px!important; margin-left:10px" />
        </a>
        <a class="close-btn toggle-sidebar" href="javascript:void(0)">
            <i class="fa-solid fa-bars fa-lg"></i>
        </a>
    </div>
    <div class="page-main-header col d-flex justify-content-end align-items-center gap-3 pe-3">

        {{-- Badge identificador --}}
        <span class="landlord-badge d-none d-md-inline-flex align-items-center gap-1">
            <i class="fa-solid fa-shield-halved" style="font-size:10px;"></i> Landlord
        </span>

        {{-- Botón Cambiar de Tienda (siempre visible) --}}
        @php
            $misTenants = \App\Models\Tenant::orderBy('nombre')->get();
        @endphp
        @push('modals')
        {{-- Overlay full-screen: Cambiar de Tienda --}}
        <div id="overlayTiendas"
            onclick="cerrarTiendaOverlay()"
            style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.65); backdrop-filter:blur(6px); z-index:9990; overflow-y:auto; cursor:pointer;">

            {{-- Título esquina superior izquierda --}}
            <h4 class="text-white fw-bold d-flex align-items-center gap-2"
                style="position:fixed; top:22px; left:28px; margin:0; pointer-events:none;">
                <i class="fa-solid fa-store"></i> Cambiar de Tienda
            </h4>

            {{-- X esquina superior derecha --}}
            <button onclick="cerrarTiendaOverlay(); event.stopPropagation();"
                style="position:fixed; top:14px; right:20px; background:rgba(255,255,255,0.15); border:none; border-radius:50%; width:38px; height:38px; color:#fff; font-size:16px; display:flex; align-items:center; justify-content:center; cursor:pointer; z-index:9991;">
                <i class="fa-solid fa-xmark"></i>
            </button>

            {{-- Tarjetas centradas --}}
            <div onclick="event.stopPropagation()"
                style="min-height:100%; display:flex; align-items:center; justify-content:center; padding:80px 20px; cursor:default;">
                <div class="d-flex flex-wrap gap-4 justify-content-center" style="max-width:860px;">

                        @foreach($misTenants as $t)
                        @php $colorT = $t->themeColor(); @endphp
                        <form action="{{ route('tenant.switch') }}" method="POST">
                            @csrf
                            <input type="hidden" name="tenant_id" value="{{ $t->id }}">
                            <input type="hidden" name="redirect" value="{{ route('ventas') }}">
                            <button type="submit" class="btn p-0 border-0 text-start" style="cursor:pointer;">
                                <div style="width:160px; border-radius:14px; overflow:hidden; border:3px solid rgba(255,255,255,0.2); box-shadow:0 4px 20px rgba(0,0,0,.3); transition:border-color .2s, transform .15s;"
                                    onmouseenter="this.style.borderColor='{{ $colorT }}'; this.style.transform='scale(1.04)';"
                                    onmouseleave="this.style.borderColor='rgba(255,255,255,0.2)'; this.style.transform='scale(1)';">
                                    <div style="background:{{ $colorT }}; height:100px; display:flex; align-items:center; justify-content:center; position:relative;">
                                        <i class="fa-solid fa-store" style="font-size:42px; color:#fff;"></i>
                                        @if(! $t->isActivo())
                                        <span style="position:absolute; top:8px; right:8px; background:#dc3545; border-radius:50px; padding:2px 7px;">
                                            <span style="color:#fff; font-size:0.6rem; font-weight:700;">INACTIVO</span>
                                        </span>
                                        @endif
                                    </div>
                                    <div style="background:#fff; padding:10px 12px; text-align:center;">
                                        <div class="fw-semibold text-dark" style="font-size:0.88rem; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                                            {{ $t->nombre }}
                                        </div>
                                    </div>
                                </div>
                            </button>
                        </form>
                        @endforeach

                        <a href="{{ route('crear-tienda') }}" style="width:160px; display:block; text-decoration:none;">
                            <div style="border-radius:14px; overflow:hidden; border:3px dashed rgba(40,167,69,0.7); box-shadow:0 4px 20px rgba(0,0,0,.2); transition:transform .15s;"
                                onmouseenter="this.style.transform='scale(1.04)'"
                                onmouseleave="this.style.transform='scale(1)'">
                                <div style="background:rgba(40,167,69,0.15); height:100px; display:flex; align-items:center; justify-content:center;">
                                    <div style="background:#28a745; border-radius:50%; width:46px; height:46px; display:flex; align-items:center; justify-content:center;">
                                        <i class="fa-solid fa-plus" style="color:#fff; font-size:20px;"></i>
                                    </div>
                                </div>
                                <div style="background:#fff; padding:10px 12px; text-align:center;">
                                    <span class="fw-semibold text-success" style="font-size:0.88rem;">Crear Nueva Tienda</span>
                                </div>
                            </div>
                        </a>

                    </div>
            </div>
        </div>
        <script>
            function abrirTiendaOverlay() {
                document.getElementById('overlayTiendas').style.display = 'block';
                document.body.style.overflow = 'hidden';
            }
            function cerrarTiendaOverlay() {
                document.getElementById('overlayTiendas').style.display = 'none';
                document.body.style.overflow = '';
            }
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') cerrarTiendaOverlay();
            });
        </script>
        @endpush


    </div>
</header>
