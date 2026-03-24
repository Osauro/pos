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

<style>
    .color-radio { display:none; }
    .color-circle {
        display:inline-block; width:36px; height:36px;
        border-radius:50%; cursor:pointer;
        border: 3px solid transparent;
        outline: 2px solid transparent;
        outline-offset: 2px;
        transition: all .15s;
    }
    .color-radio:checked + .color-circle {
        border-color: #333;
        outline-color: currentColor;
    }
</style>

<div style="width:100%; max-width:520px; border-radius:20px; background:#fff; box-shadow:0 20px 60px rgba(0,0,0,.5); margin:16px;">

    {{-- Header --}}
    <div style="background:{{ $colorActual['hex'] }}; padding:20px 24px; border-radius:20px 20px 0 0;">
        <h4 class="fw-bold mb-0 text-white" style="display:flex; align-items:center; gap:8px;">
            <i class="fa-solid fa-store"></i> Nueva Tienda
        </h4>
        <small class="text-white" style="opacity:.75;">Completa los datos para activar tu tienda</small>
    </div>

    <form wire:submit.prevent="activar" style="padding:24px;">

        {{-- Alerta 30 dias --}}
        <div style="background:linear-gradient(135deg,#7366ff15,#7366ff08); border:1px solid #7366ff40; border-radius:12px; padding:16px 20px; display:flex; align-items:flex-start; gap:12px; margin-bottom:20px;">
            <div style="background:#7366ff; border-radius:50%; width:38px; height:38px; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                <i class="fa-solid fa-gift" style="color:#fff; font-size:16px;"></i>
            </div>
            <div>
                <div style="font-weight:700; color:#7366ff; font-size:0.95rem;">30 dias de prueba gratuita</div>
                <div style="color:#6c757d; font-size:0.82rem; margin-top:2px;">
                    Tu tienda estara activa de inmediato. Despues puedes renovar por <strong>50 Bs/ano</strong>.
                </div>
            </div>
        </div>

        {{-- Nombre --}}
        <div style="margin-bottom:16px;">
            <label style="display:block; font-weight:600; margin-bottom:6px;">
                Nombre de la Tienda <span style="color:#dc3545;">*</span>
            </label>
            <input type="text"
                wire:model.lazy="nombreTenant"
                placeholder="Ej: Mi Tienda Principal"
                style="width:100%; padding:8px 12px; border:1px solid #dee2e6; border-radius:10px; font-size:1rem; box-sizing:border-box; @error('nombreTenant') border-color:#dc3545; @enderror">
            @error('nombreTenant')
                <div style="color:#dc3545; font-size:0.85rem; margin-top:4px;">{{ $message }}</div>
            @enderror
        </div>

        {{-- Celular --}}
        <div style="margin-bottom:16px;">
            <label style="display:block; font-weight:600; color:#6c757d; margin-bottom:6px;">
                Celular <span style="font-weight:400;">(Opcional)</span>
            </label>
            <input type="text"
                wire:model.lazy="celularTenant"
                placeholder="Ej: 76543210"
                style="width:100%; padding:8px 12px; border:1px solid #dee2e6; border-radius:10px; font-size:1rem; box-sizing:border-box;">
        </div>

        {{-- Direccion --}}
        <div style="margin-bottom:16px;">
            <label style="display:block; font-weight:600; color:#6c757d; margin-bottom:6px;">
                Dirección <span style="font-weight:400;">(Opcional)</span>
            </label>
            <input type="text"
                wire:model.lazy="direccionTenant"
                placeholder="Ej: Av. Principal #123"
                style="width:100%; padding:8px 12px; border:1px solid #dee2e6; border-radius:10px; font-size:1rem; box-sizing:border-box;">
        </div>

        {{-- Color --}}
        <div style="margin-bottom:24px;">
            <label style="display:block; font-weight:600; color:#6c757d; margin-bottom:8px;">
                Color de la Tienda
            </label>
            <div style="display:flex; flex-wrap:wrap; gap:8px;">
                @foreach ($colores as $num => $color)
                    <label style="cursor:pointer; display:flex; align-items:center;">
                        <input type="radio"
                            wire:model="colorTenant"
                            name="colorTenant"
                            value="{{ $num }}"
                            style="display:none;">
                        <span style="
                            display:inline-block; width:36px; height:36px;
                            border-radius:50%; background:{{ $color['hex'] }};
                            border: 3px solid {{ $colorTenant == $num ? '#333' : 'transparent' }};
                            outline: 2px solid {{ $colorTenant == $num ? $color['hex'] : 'transparent' }};
                            outline-offset: 2px;
                            cursor:pointer; transition:all .15s;"
                            title="{{ $color['nombre'] }}">
                        </span>
                    </label>
                @endforeach
            </div>
            <div style="margin-top:8px; display:flex; align-items:center; gap:8px; font-size:0.82rem; color:#666;">
                <span style="width:14px; height:14px; border-radius:50%; background:{{ $colorActual['hex'] }}; display:inline-block;"></span>
                <span>{{ $colorActual['nombre'] }}</span>
            </div>
        </div>

        {{-- Botones --}}
        <div style="display:flex; gap:8px;">
            <a href="{{ route('ventas') }}"
                style="padding:10px 20px; border:1px solid #6c757d; border-radius:10px; color:#6c757d; text-decoration:none; white-space:nowrap; display:flex; align-items:center; gap:6px;">
                <i class="fa-solid fa-arrow-left"></i> Volver
            </a>
            <button type="submit"
                wire:loading.attr="disabled"
                style="flex:1; padding:10px; border:none; border-radius:10px; background:{{ $colorActual['hex'] }}; color:#fff; font-weight:700; font-size:1rem; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:8px;">
                <span wire:loading wire:target="activar" style="width:16px; height:16px; border:2px solid rgba(255,255,255,.5); border-top-color:#fff; border-radius:50%; animation:spin .6s linear infinite; display:inline-block;"></span>
                <i wire:loading.remove wire:target="activar" class="fa-solid fa-check"></i>
                <span wire:loading.remove wire:target="activar">Activar Tienda Gratis</span>
                <span wire:loading wire:target="activar">Creando...</span>
            </button>
        </div>

    </form>
</div>

<style>
@keyframes spin { to { transform: rotate(360deg); } }
</style>
