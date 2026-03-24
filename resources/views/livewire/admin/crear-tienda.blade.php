<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0 d-flex align-items-center gap-2">
            <i class="fa-solid fa-store"></i> Nueva Tienda
        </h4>
        <span class="text-muted" style="font-size:0.85rem;">Completa los datos para activar tu tienda</span>
    </div>

    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-6">

            {{-- Alerta 30 días gratis --}}
            <div class="alert d-flex align-items-start gap-3 mb-4"
                style="background:linear-gradient(135deg,#7366ff15,#7366ff08); border:1px solid #7366ff40; border-radius:12px; padding:16px 20px;">
                <div style="background:#7366ff; border-radius:50%; width:38px; height:38px; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                    <i class="fa-solid fa-gift" style="color:#fff; font-size:16px;"></i>
                </div>
                <div>
                    <div class="fw-bold" style="color:#7366ff; font-size:0.95rem;">30 días de prueba gratuita</div>
                    <div class="text-muted" style="font-size:0.82rem; margin-top:2px;">
                        Tu tienda estará activa de inmediato. Después puedes renovar por <strong>50 Bs/año</strong>.
                    </div>
                </div>
            </div>

            {{-- Formulario --}}
            <div class="card border-0 shadow-sm" style="border-radius:16px;">
                <div class="card-body p-4">

                    <div class="mb-4">
                        <label class="form-label fw-semibold">
                            Nombre de la Tienda <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                            class="form-control form-control-lg @error('nombre') is-invalid @enderror"
                            wire:model="nombre"
                            placeholder="Ej: Mi Tienda Principal"
                            autofocus
                            style="border-radius:10px;">
                        @error('nombre')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold text-muted">Teléfono <span class="text-muted fw-normal">(Opcional)</span></label>
                        <input type="text"
                            class="form-control @error('telefono') is-invalid @enderror"
                            wire:model="telefono"
                            placeholder="Ej: 76543210"
                            style="border-radius:10px;">
                        @error('telefono')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold text-muted">Dirección <span class="text-muted fw-normal">(Opcional)</span></label>
                        <input type="text"
                            class="form-control @error('direccion') is-invalid @enderror"
                            wire:model="direccion"
                            placeholder="Ej: Av. Principal #123"
                            style="border-radius:10px;">
                        @error('direccion')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Selector de color --}}
                    <div class="mb-4">
                        <label class="form-label fw-semibold text-muted">Color de la Tienda</label>
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
                        <div class="d-flex flex-wrap gap-2 mt-1">
                            @foreach($temas as $num => $tema)
                            <button type="button"
                                wire:click="$set('theme_number', {{ $num }})"
                                title="{{ $tema['nombre'] }}"
                                style="
                                    width: 36px; height: 36px; border-radius: 50%;
                                    background: {{ $tema['color'] }};
                                    border: 3px solid {{ $theme_number == $num ? '#333' : 'transparent' }};
                                    outline: 2px solid {{ $theme_number == $num ? $tema['color'] : 'transparent' }};
                                    outline-offset: 2px;
                                    cursor: pointer; transition: all .15s;
                                ">
                            </button>
                            @endforeach
                        </div>
                        @php $colorSeleccionado = $temas[$theme_number]['color'] ?? '#29adb2'; $nombreTema = $temas[$theme_number]['nombre'] ?? 'Teal'; @endphp
                        <div class="mt-2 d-flex align-items-center gap-2" style="font-size:0.82rem; color:#666;">
                            <span style="width:14px;height:14px;border-radius:50%;background:{{ $colorSeleccionado }};display:inline-block;"></span>
                            {{ $nombreTema }}
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-2">
                        <a href="{{ route('admin.tenants') }}"
                            class="btn btn-outline-secondary flex-shrink-0"
                            style="border-radius:10px; padding:10px 20px;">
                            <i class="fa-solid fa-arrow-left me-1"></i> Volver
                        </a>
                        <button type="button"
                            wire:click="save"
                            wire:loading.attr="disabled"
                            class="btn btn-primary w-100 d-flex align-items-center justify-content-center gap-2"
                            style="background:#7366ff; border-color:#7366ff; border-radius:10px; padding:10px; font-weight:600;">
                            <span wire:loading wire:target="save" class="spinner-border spinner-border-sm"></span>
                            <i wire:loading.remove wire:target="save" class="fa-solid fa-check"></i>
                            <span wire:loading.remove wire:target="save">Activar Tienda Gratis</span>
                            <span wire:loading wire:target="save">Creando...</span>
                        </button>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>
