<div class="container-fluid">

    <div class="module-sticky-header">
        <div class="d-flex justify-content-between align-items-center gap-2">
            <h5 class="mb-0 fw-bold">
                <i class="fa-solid fa-print me-2"></i>Configuración
            </h5>
            <div></div>
        </div>
    </div>

    {{-- ── TABS con Alpine.js + localStorage ── --}}
    <div class="module-scroll-area p-2"
         x-data="configTabs()"
         x-init="init()">

        <ul class="nav nav-tabs mb-3" role="tablist">
            <li class="nav-item">
                <button class="nav-link" :class="{ active: tab === 'impresora' }" @click="setTab('impresora')">
                    <i class="fa-solid fa-print me-1"></i>Impresora
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" :class="{ active: tab === 'qr' }" @click="setTab('qr')">
                    <i class="fa-solid fa-qrcode me-1"></i>Pago QR
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link text-danger" :class="{ active: tab === 'peligro' }" @click="setTab('peligro')">
                    <i class="fa-solid fa-triangle-exclamation me-1"></i>Peligro
                </button>
            </li>
        </ul>

        {{-- ═══════════════ TAB IMPRESORA ═══════════════ --}}
        <div x-show="tab === 'impresora'" x-cloak>
            <div class="row justify-content-center">
                <div class="col-xl-6 col-lg-8 col-md-10">

                    <div class="d-flex justify-content-end mb-3">
                        <button class="btn btn-primary btn-sm px-3"
                                wire:click="guardar"
                                wire:loading.attr="disabled">
                            <span wire:loading wire:target="guardar" class="spinner-border spinner-border-sm me-1"></span>
                            <i class="fa-solid fa-floppy-disk me-1"></i>Guardar
                        </button>
                    </div>

                    {{-- Info agente --}}
                    <div class="alert alert-warning py-2 mb-3 d-flex align-items-center gap-2" style="font-size:.82rem;">
                        <i class="fa-solid fa-desktop fa-lg text-warning"></i>
                        <span>Modo <strong>Módulo PC</strong> &mdash; requiere el agente <strong>printPOS.exe</strong> activo en la PC cajera.</span>
                    </div>

                    {{-- Impresoras --}}
                    <div class="card mb-3">
                        <div class="card-header py-2">
                            <h6 class="mb-0 fw-semibold"><i class="fa-solid fa-tag me-1"></i>Impresoras</h6>
                        </div>
                        <div class="card-body pb-2">
                            <div class="row g-3">
                                <div class="col-sm-6">
                                    <label class="form-label small mb-1">
                                        <i class="fa-solid fa-ticket me-1"></i>Tickets
                                    </label>
                                    <input type="text"
                                           class="form-control form-control-sm @error('printer_nombre_ticket') is-invalid @enderror"
                                           wire:model.blur="printer_nombre_ticket"
                                           placeholder="Ej: POS-80">
                                    @error('printer_nombre_ticket')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label small mb-1">
                                        <i class="fa-solid fa-utensils me-1"></i>Comanda
                                        <span class="text-muted">(opcional)</span>
                                    </label>
                                    <input type="text"
                                           class="form-control form-control-sm @error('printer_nombre_comanda') is-invalid @enderror"
                                           wire:model.blur="printer_nombre_comanda"
                                           placeholder="Vacío = misma de tickets">
                                    @error('printer_nombre_comanda')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Opciones --}}
                    <div class="card mb-3">
                        <div class="card-header py-2">
                            <h6 class="mb-0 fw-semibold"><i class="fa-solid fa-sliders me-1"></i>Opciones</h6>
                        </div>
                        <div class="card-body">
                            <p class="text-muted small mb-2">Impresión automática al cerrar una venta</p>
                            <div class="row g-2 mb-3">
                                <div class="col-sm-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="auto_ticket" wire:model="printer_auto_ticket">
                                        <label class="form-check-label small" for="auto_ticket">
                                            <i class="fa-solid fa-ticket me-1"></i><strong>Ticket</strong> automático
                                        </label>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="auto_comanda" wire:model="printer_auto_comanda">
                                        <label class="form-check-label small" for="auto_comanda">
                                            <i class="fa-solid fa-utensils me-1"></i><strong>Comanda</strong> automática
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <hr class="my-2">
                            <p class="text-muted small mb-2">Ancho del papel</p>
                            <div class="d-flex gap-3 mb-3">
                                @foreach(['58' => '58 mm', '80' => '80 mm', '110' => '110 mm'] as $val => $label)
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" id="width_{{ $val }}" wire:model="printer_width" value="{{ $val }}">
                                    <label class="form-check-label small" for="width_{{ $val }}">{{ $label }}</label>
                                </div>
                                @endforeach
                            </div>
                            <hr class="my-2">
                            <p class="text-muted small mb-2">Cabecera del ticket</p>
                            <div class="row g-2">
                                <div class="col-sm-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="printer_logo" wire:model="printer_logo">
                                        <label class="form-check-label small" for="printer_logo">
                                            <i class="fa-solid fa-image me-1"></i><strong>Imprimir logo</strong>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="printer_show_nombre" wire:model="printer_show_nombre">
                                        <label class="form-check-label small" for="printer_show_nombre">
                                            <i class="fa-solid fa-store me-1"></i><strong>Imprimir nombre del negocio</strong>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Clave de cifrado --}}
                    <div class="card mb-3">
                        <div class="card-header py-2">
                            <h6 class="mb-0 fw-semibold"><i class="fa-solid fa-lock me-1"></i>Clave de cifrado</h6>
                        </div>
                        <div class="card-body">
                            <div class="input-group input-group-sm">
                                <input type="text"
                                       class="form-control @error('printer_secret_key') is-invalid @enderror"
                                       wire:model.blur="printer_secret_key"
                                       placeholder="64 caracteres hexadecimales"
                                       maxlength="64"
                                       style="font-family: 'Courier New', monospace; font-size:.78rem;">
                                <button class="btn btn-outline-secondary" type="button" wire:click="generarClave">
                                    <i class="fa-solid fa-key me-1"></i>Generar
                                </button>
                                @error('printer_secret_key')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <small class="text-muted">Clave AES-256 compartida con el agente printPOS (64 hex)</small>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        {{-- ═══════════════ TAB QR PAGO ═══════════════ --}}
        <div x-show="tab === 'qr'" x-cloak>
            <div class="row justify-content-center">
                <div class="col-xl-5 col-lg-7 col-md-9">

                    <div class="card">
                        <div class="card-header py-2">
                            <h6 class="mb-0 fw-semibold">
                                <i class="fa-solid fa-qrcode me-1"></i>Imagen QR de cobro
                            </h6>
                        </div>
                        <div class="card-body">

                            <p class="text-muted small mb-3">
                                Esta imagen se mostrará en el POS al cobrar una venta para que el cliente la escanee y realice el pago online.
                            </p>

                            {{-- Vista previa actual --}}
                            @if($qr_imagen_actual)
                                <div class="text-center mb-3">
                                    <img src="{{ asset('storage/' . $qr_imagen_actual) }}"
                                         alt="QR Pago"
                                         class="img-fluid rounded border shadow-sm"
                                         style="max-width:220px;max-height:220px;object-fit:contain;">
                                    <div class="mt-2">
                                        <button wire:click="eliminarQR"
                                                wire:confirm="¿Eliminar la imagen QR?"
                                                class="btn btn-outline-danger btn-sm">
                                            <i class="fa-solid fa-trash-can me-1"></i>Eliminar QR
                                        </button>
                                    </div>
                                </div>
                            @else
                                <div class="text-center text-muted py-4 mb-3 border rounded bg-light">
                                    <i class="fa-solid fa-qrcode fa-3x opacity-25 d-block mb-2"></i>
                                    <span class="small">Sin imagen QR configurada</span>
                                </div>
                            @endif

                            {{-- Upload --}}
                            <div>
                                <label class="form-label small mb-1 fw-semibold">
                                    {{ $qr_imagen_actual ? 'Reemplazar imagen' : 'Subir imagen QR' }}
                                </label>
                                <input type="file"
                                       wire:model="qr_imagen_file"
                                       accept="image/*"
                                       class="form-control form-control-sm @error('qr_imagen_file') is-invalid @enderror">
                                @error('qr_imagen_file')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="text-muted" style="font-size:.75rem;margin-top:4px;">PNG, JPG, WEBP &mdash; máx. 2 MB</div>
                            </div>

                            {{-- Preview temporal --}}
                            @if($qr_imagen_file)
                                <div class="text-center mt-3">
                                    <p class="small text-muted mb-1">Vista previa:</p>
                                    <img src="{{ $qr_imagen_file->temporaryUrl() }}"
                                         class="img-fluid rounded border"
                                         style="max-width:180px;max-height:180px;object-fit:contain;">
                                </div>
                            @endif

                            <div class="d-flex justify-content-end mt-3">
                                <button class="btn btn-primary btn-sm px-4"
                                        wire:click="guardarQR"
                                        wire:loading.attr="disabled"
                                        wire:target="guardarQR,qr_imagen_file">
                                    <span wire:loading wire:target="guardarQR" class="spinner-border spinner-border-sm me-1"></span>
                                    <i class="fa-solid fa-floppy-disk me-1"></i>Guardar QR
                                </button>
                            </div>

                        </div>
                    </div>

                </div>
            </div>
        </div>

        {{-- ═══════════════ TAB PELIGRO ═══════════════ --}}
        <div x-show="tab === 'peligro'" x-cloak>
            <div class="row justify-content-center">
                <div class="col-xl-5 col-lg-7 col-md-9">

                    <div class="alert alert-danger d-flex gap-3 align-items-start mb-4">
                        <i class="fa-solid fa-triangle-exclamation fa-lg mt-1 flex-shrink-0"></i>
                        <div>
                            <p class="fw-bold mb-1">Zona de peligro</p>
                            <p class="mb-0 small">Las acciones de esta sección son <strong>irreversibles</strong>. Úsalas únicamente cuando necesites reiniciar el ciclo de datos del negocio (por ejemplo, al comenzar una nueva temporada o periodo).</p>
                        </div>
                    </div>

                    <div class="card border-danger">
                        <div class="card-header bg-danger bg-opacity-10 py-2">
                            <h6 class="mb-0 fw-semibold text-danger">
                                <i class="fa-solid fa-trash-can me-1"></i>Resetear datos del tenant
                            </h6>
                        </div>
                        <div class="card-body">
                            <p class="text-muted small mb-1"><strong>¿Qué se elimina?</strong></p>
                            <ul class="small text-muted mb-3 ps-3">
                                <li>Todas las <strong>ventas</strong> e ítems de venta</li>
                                <li>Todos los <strong>turnos</strong></li>
                                <li>Todos los <strong>movimientos</strong> de caja</li>
                                <li>El contador de popularidad de productos se pone a <strong>0</strong></li>
                            </ul>
                            <p class="text-muted small mb-3"><strong>¿Qué se conserva?</strong> Productos, usuarios, configuración de impresora y QR.</p>
                            <button class="btn btn-danger w-100"
                                    onclick="confirmarResetTenant()">
                                <i class="fa-solid fa-trash-can me-1"></i>Resetear todos los datos
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </div>

    </div>{{-- /x-data --}}

</div>

@script
<script>
    window.configTabs = function () {
        return {
            tab: 'impresora',
            init() {
                const saved = localStorage.getItem('cfg_tab');
                if (['impresora', 'qr', 'peligro'].includes(saved)) this.tab = saved;
            },
            setTab(name) {
                this.tab = name;
                localStorage.setItem('cfg_tab', name);
            },
        };
    };

    window.confirmarResetTenant = function() {
        Swal.fire({
            icon: 'warning',
            title: '¿Resetear todos los datos?',
            html: `
                <p class="text-muted small mb-3">Se eliminarán <strong>todas las ventas, turnos y movimientos</strong>.<br>
                Los <strong>productos y usuarios</strong> se conservarán.</p>
                <p class="small mb-1">Escribe <strong>RESET</strong> para confirmar:</p>
                <input id="swal-reset-input" type="text" class="swal2-input" placeholder="RESET">
            `,
            showCancelButton: true,
            confirmButtonText: 'Sí, resetear',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            preConfirm: () => {
                const val = document.getElementById('swal-reset-input').value;
                if (val !== 'RESET') {
                    Swal.showValidationMessage('Debes escribir exactamente RESET para confirmar');
                    return false;
                }
                return true;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                $wire.resetTenant();
            }
        });
    };
</script>
@endscript
