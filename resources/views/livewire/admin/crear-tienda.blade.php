<div>
    {{-- Fondo centrado --}}
    <div style="display:flex; align-items:center; justify-content:center; min-height:80vh;">

        {{-- Modal Bootstrap estatico (sin JS, siempre visible) --}}
        <div class="modal-dialog modal-dialog-centered" style="width:100%; max-width:500px; margin:0;">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title fw-bold">
                        <i class="fa-solid fa-store me-2"></i> Nueva Tienda
                    </h5>
                </div>

                <div class="modal-body">

                    {{-- Alerta prueba gratuita --}}
                    <div class="alert alert-info small mb-3 py-2">
                        <i class="fa-solid fa-gift me-1"></i>
                        Tu tienda estara activa de inmediato. Tendras <strong>30 dias de prueba gratuita</strong>.
                        Despues puedes renovar por <strong>50 Bs/ano</strong>.
                    </div>

                    {{-- Nombre --}}
                    <div class="mb-3">
                        <label class="form-label">Nombre de la Tienda *</label>
                        <input type="text"
                            class="form-control @error('nombre') is-invalid @enderror"
                            wire:model="nombre"
                            placeholder="Ej: Mi Restaurante">
                        @error('nombre')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Telefono --}}
                    <div class="mb-3">
                        <label class="form-label">Telefono <span class="text-muted fw-normal">(Opcional)</span></label>
                        <input type="text"
                            class="form-control"
                            wire:model="telefono"
                            placeholder="Ej: 76543210">
                    </div>

                    {{-- Direccion --}}
                    <div class="mb-3">
                        <label class="form-label">Direccion <span class="text-muted fw-normal">(Opcional)</span></label>
                        <input type="text"
                            class="form-control"
                            wire:model="direccion"
                            placeholder="Ej: Av. Principal #123">
                    </div>

                    {{-- Color --}}
                    <div class="mb-3">
                        <label class="form-label">Color de la Tienda</label>
                        @php
                            $temas = [
                                2  => ['color' => '#f73164', 'nombre' => 'Rosa'],
                                3  => ['color' => '#29adb2', 'nombre' => 'Teal'],
                                4  => ['color' => '#6610f2', 'nombre' => 'Morado'],
                                5  => ['color' => '#dc3545', 'nombre' => 'Rojo'],
                                6  => ['color' => '#f57f17', 'nombre' => 'Naranja'],
                                7  => ['color' => '#0288d1', 'nombre' => 'Azul'],
                                8  => ['color' => '#00897b', 'nombre' => 'Verde Teal'],
                                9  => ['color' => '#558b2f', 'nombre' => 'Verde'],
                                10 => ['color' => '#455a64', 'nombre' => 'Gris Azul'],
                            ];
                        @endphp
                        <div class="d-flex flex-wrap gap-2 mt-1"
                             x-data="{ colorSel: {{ $theme_number }} }"
                             x-init="$watch('colorSel', v => $wire.set('theme_number', v, false))">
                            @foreach($temas as $num => $tema)
                            <button type="button"
                                @click.prevent="colorSel = {{ $num }}"
                                title="{{ $tema['nombre'] }}"
                                :style="`
                                    width:32px; height:32px; border-radius:50%;
                                    background:{{ $tema['color'] }};
                                    border:3px solid ${colorSel == {{ $num }} ? '#333' : 'transparent'};
                                    outline:2px solid ${colorSel == {{ $num }} ? '{{ $tema['color'] }}' : 'transparent'};
                                    outline-offset:2px; cursor:pointer; transition:all .15s;
                                `">
                            </button>
                            @endforeach
                        </div>
                    </div>

                </div>

                <div class="modal-footer">
                    <a href="{{ route('ventas') }}" class="btn btn-secondary">
                        Volver
                    </a>
                    <button type="button"
                        class="btn btn-primary"
                        wire:click="saveTenant"
                        wire:loading.attr="disabled">
                        <span wire:loading wire:target="saveTenant" class="spinner-border spinner-border-sm me-1"></span>
                        Activar Tienda Gratis
                    </button>
                </div>

            </div>
        </div>

    </div>
</div>
