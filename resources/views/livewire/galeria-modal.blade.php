<div>
    @if ($mostrar)
    @teleport('body')
        <div style="position: fixed; inset: 0; background: rgba(0,0,0,0.75); z-index: 99999; display: flex; flex-direction: column;">
            <div style="background: #fff; display: flex; flex-direction: column; width: 100%; height: 100%; overflow: hidden;">

                <!-- Header -->
                <div style="display: flex; align-items: center; justify-content: space-between; padding: 12px 20px; border-bottom: 1px solid #dee2e6; flex-shrink: 0;">
                    <h5 style="margin: 0; font-size: 1.1rem;">
                        <i class="fa-solid fa-images me-2"></i> Galería de Imágenes
                    </h5>
                    <button type="button" class="btn-close" wire:click="cerrar"></button>
                </div>

                <!-- Buscador -->
                <div style="padding: 12px 20px; flex-shrink: 0; border-bottom: 1px solid #f0f0f0;">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                        <input type="text"
                               class="form-control"
                               wire:model.live.debounce.300ms="busqueda"
                               placeholder="Buscar por nombre o etiqueta...">
                        @if ($busqueda)
                            <button class="btn btn-outline-secondary" wire:click="$set('busqueda', '')">
                                <i class="fa-solid fa-times"></i>
                            </button>
                        @endif
                    </div>
                </div>

                <!-- Grid de imágenes con scroll -->
                <div style="flex: 1; overflow-y: auto; padding: 16px 20px;">
                    <div style="display: flex; flex-wrap: wrap; gap: 12px; align-items: flex-start;">

                        <!-- Card subir imagen -->
                        <div style="width: 160px; flex-shrink: 0;">
                            <label style="display: flex; flex-direction: column; align-items: center; justify-content: center; width: 160px; height: 160px; border: 2px dashed #0d6efd; border-radius: 8px; background: #f0f4ff; cursor: pointer; transition: background .15s; color: #0d6efd; text-align: center;"
                                   onmouseover="this.style.background='#dde8ff'"
                                   onmouseout="this.style.background='#f0f4ff'">
                                <span wire:loading.remove wire:target="nuevaImagen">
                                    <i class="fa-solid fa-cloud-arrow-up fa-2x mb-1"></i>
                                    <span style="display: block; font-size: 0.85rem; font-weight: 600;">Subir imagen</span>
                                    <span style="display: block; font-size: 0.7rem; opacity: .6;">Máx. 10 MB</span>
                                </span>
                                <span wire:loading wire:target="nuevaImagen">
                                    <i class="fa-solid fa-spinner fa-spin fa-2x mb-1"></i>
                                    <span style="display: block; font-size: 0.85rem; font-weight: 600;">Subiendo...</span>
                                </span>
                                <input type="file" class="d-none" wire:model="nuevaImagen" accept="image/*">
                            </label>
                            @error('nuevaImagen')
                                <div class="text-danger mt-1" style="font-size: 0.72rem; width: 160px;">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Imágenes de la galería -->
                        @forelse($imagenes as $img)
                            <div style="width: 160px; flex-shrink: 0; position: relative;"
                                 wire:click="seleccionar({{ $img->id }})"
                                 title="{{ $img->nombre ?? '' }}">
                                <div style="width: 160px; height: 160px; border-radius: 8px; overflow: hidden; background: #f8f9fa; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: transform .15s, box-shadow .15s;"
                                     onmouseover="this.style.transform='scale(1.04)'; this.style.boxShadow='0 6px 18px rgba(0,0,0,.25)';"
                                     onmouseout="this.style.transform=''; this.style.boxShadow='';">
                                    <img src="{{ $img->photo_url }}"
                                         alt="{{ $img->nombre ?? 'Imagen' }}"
                                         style="max-width: 100%; max-height: 100%; object-fit: contain;">
                                </div>
                                @if ($img->veces_usado > 0)
                                    <span class="badge bg-secondary"
                                          style="position: absolute; top: 4px; right: 4px; font-size: 0.6rem;">
                                        {{ $img->veces_usado }}
                                    </span>
                                @endif
                                @if ($img->nombre)
                                    <div style="font-size: 0.72rem; color: #666; margin-top: 4px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; width: 160px;">
                                        {{ $img->nombre }}
                                    </div>
                                @endif
                            </div>
                        @empty
                            <div style="flex: 1; text-align: center; padding: 60px 0; color: #aaa;">
                                <i class="fa-solid fa-image fa-3x mb-3 d-block"></i>
                                No hay imágenes todavía
                            </div>
                        @endforelse

                    </div>
                </div>

                <!-- Footer -->
                <div style="padding: 10px 20px; border-top: 1px solid #dee2e6; text-align: right; flex-shrink: 0;">
                    <button type="button" class="btn btn-secondary btn-sm" wire:click="cerrar">Cancelar</button>
                </div>

            </div>
        </div>
    @endteleport
    @endif
</div>
