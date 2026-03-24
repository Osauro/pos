<div style="width:100%; max-width:520px; max-height:90vh; overflow-y:auto; border-radius:20px; background:#fff; box-shadow:0 20px 60px rgba(0,0,0,.5); margin:16px;">

    @php
    $colores = [
        2  => ['hex' => '#f73164', 'nombre' => 'Rosa'],
        3  => ['hex' => '#29adb2', 'nombre' => 'Teal'],
        4  => ['hex' => '#6610f2', 'nombre' => 'Morado'],
        5  => ['hex' => '#dc3545', 'nombre' => 'Rojo'],
        6  => ['hex' => '#f57f17', 'nombre' => 'Naranja'],
        7  => ['hex' => '#0288d1', 'nombre' => 'Azul'],
        8  => ['hex' => '#00897b', 'nombre' => 'Verde Teal'],
        9  => ['hex' => '#558b2f', 'nombre' => 'Verde'],
        10 => ['hex' => '#455a64', 'nombre' => 'Gris Azul'],
    ];
    $colorActual = $colores[$colorTenant] ?? $colores[3];
    @endphp

    {{-- Header --}}
    <div style="background:{{ $colorActual['hex'] }}; padding:20px 24px; border-radius:20px 20px 0 0;">
        <h4 class="fw-bold mb-0 text-white d-flex align-items-center gap-2">
            <i class="fa-solid fa-store"></i> Nueva Tienda
        </h4>
        <small class="text-white opacity-75">Completa los datos para activar tu tienda</small>
    </div>

    <div class="p-4">

        {{-- Alerta 30 dias --}}
        <div class="alert d-flex align-items-start gap-3 mb-4"
            style="background:linear-gradient(135deg,#7366ff15,#7366ff08); border:1px solid #7366ff40; border-radius:12px; padding:16px 20px;">
            <div style="background:#7366ff; border-radius:50%; width:38px; height:38px; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                <i class="fa-solid fa-gift" style="color:#fff; font-size:16px;"></i>
            </div>
            <div>
                <div class="fw-bold" style="color:#7366ff; font-size:0.95rem;">30 dias de prueba gratuita</div>
                <div class="text-muted" style="font-size:0.82rem; margin-top:2px;">
                    Tu tienda estara activa de inmediato. Despues puedes renovar por <strong>50 Bs/ano</strong>.
                </div>
            </div>
        </div>

        {{-- Nombre --}}
        <div class="mb-3">
            <label class="form-label fw-semibold">Nombre de la Tienda <span class="text-danger">*</span></label>
            <input type="text"
                wire:model="nombreTenant"
                class="form-control @error('nombreTenant') is-invalid @enderror"
                placeholder="Ej: Mi Tienda Principal"
                autofocus
                style="border-radius:10px;">
            @error('nombreTenant')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Celular --}}
        <div class="mb-3">
            <label class="form-label fw-semibold text-muted">Celular <span class="fw-normal">(Opcional)</span></label>
            <input type="text"
                wire:model="celularTenant"
                class="form-control @error('celularTenant') is-invalid @enderror"
                placeholder="Ej: 76543210"
                style="border-radius:10px;">
            @error('celularTenant')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Direccion --}}
        <div class="mb-3">
            <label class="form-label fw-semibold text-muted">Dirección <span class="fw-normal">(Opcional)</span></label>
            <input type="text"
                wire:model="direccionTenant"
                class="form-control @error('direccionTenant') is-invalid @enderror"
                placeholder="Ej: Av. Principal #123"
                style="border-radius:10px;">
            @error('direccionTenant')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Color --}}
        <div class="mb-4">
            <label class="form-label fw-semibold text-muted">Color de la Tienda</label>
            <div class="d-flex flex-wrap gap-2 mt-1">
                @foreach ($colores as $num => $color)
                    <button type="button"
                        wire:click="seleccionarColor({{ $num }})"
                        title="{{ $color['nombre'] }}"
                        style="width:36px; height:36px; border-radius:50%; background:{{ $color['hex'] }};
                               border: 3px solid {{ $colorTenant == $num ? '#333' : 'transparent' }};
                               outline: 2px solid {{ $colorTenant == $num ? $color['hex'] : 'transparent' }};
                               outline-offset: 2px; cursor:pointer; transition:all .15s;">
                    </button>
                @endforeach
            </div>
            <div class="mt-2 d-flex align-items-center gap-2" style="font-size:0.82rem; color:#666;">
                <span style="width:14px; height:14px; border-radius:50%; background:{{ $colorActual['hex'] }}; display:inline-block;"></span>
                <span>{{ $colorActual['nombre'] }}</span>
            </div>
        </div>

        {{-- Botones --}}
        <div class="d-flex gap-2 mt-2">
            <a href="{{ route('ventas') }}"
                class="btn btn-outline-secondary flex-shrink-0"
                style="border-radius:10px; padding:10px 20px;">
                <i class="fa-solid fa-arrow-left me-1"></i> Volver
            </a>
            <button type="button"
                wire:click="activar"
                wire:loading.attr="disabled"
                class="btn w-100 d-flex align-items-center justify-content-center gap-2 text-white fw-bold"
                style="background:{{ $colorActual['hex'] }}; border-color:{{ $colorActual['hex'] }}; border-radius:10px; padding:10px;">
                <span wire:loading wire:target="activar" class="spinner-border spinner-border-sm"></span>
                <i wire:loading.remove wire:target="activar" class="fa-solid fa-check"></i>
                <span wire:loading.remove wire:target="activar">Activar Tienda Gratis</span>
                <span wire:loading wire:target="activar">Creando...</span>
            </button>
        </div>

    </div>
</div>
