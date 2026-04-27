<div class="container-fluid">

    <div class="module-sticky-header">
        <div class="d-flex justify-content-between align-items-center gap-2">
            <h5 class="mb-0 fw-bold">
                <i class="fa-solid fa-print me-2"></i>Configuraci&oacute;n de Impresora
            </h5>
            <div class="d-flex gap-2">
                <button class="btn btn-danger btn-sm px-3"
                        onclick="confirmarResetTenant()">
                    <i class="fa-solid fa-trash-can me-1"></i>Resetear Tenant
                </button>
                <button class="btn btn-primary btn-sm px-3"
                        wire:click="guardar"
                        wire:loading.attr="disabled">
                    <span wire:loading wire:target="guardar" class="spinner-border spinner-border-sm me-1"></span>
                    <i class="fa-solid fa-floppy-disk me-1"></i>Guardar
                </button>
            </div>
        </div>
    </div>

    <div class="module-scroll-area p-2">
        <div class="row justify-content-center">
            <div class="col-xl-6 col-lg-8 col-md-10">

                {{-- Info agente --}}
                <div class="alert alert-warning py-2 mb-3 d-flex align-items-center gap-2" style="font-size:.82rem;">
                    <i class="fa-solid fa-desktop fa-lg text-warning"></i>
                    <span>Modo <strong>M&oacute;dulo PC</strong> &mdash; requiere el agente <strong>printPOS.exe</strong> activo en la PC cajera.</span>
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
                                       placeholder="Vac&iacute;o = misma de tickets">
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

                        <p class="text-muted small mb-2">Impresi&oacute;n autom&aacute;tica al cerrar una venta</p>
                        <div class="row g-2 mb-3">
                            <div class="col-sm-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="auto_ticket" wire:model="printer_auto_ticket">
                                    <label class="form-check-label small" for="auto_ticket">
                                        <i class="fa-solid fa-ticket me-1"></i><strong>Ticket</strong> autom&aacute;tico
                                    </label>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="auto_comanda" wire:model="printer_auto_comanda">
                                    <label class="form-check-label small" for="auto_comanda">
                                        <i class="fa-solid fa-utensils me-1"></i><strong>Comanda</strong> autom&aacute;tica
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

</div>

@script
<script>
    function confirmarResetTenant() {
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
    }
</script>
@endscript
