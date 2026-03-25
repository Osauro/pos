<div class="container-fluid">

    <div class="module-sticky-header">
        <div class="d-flex justify-content-between align-items-center gap-2">
            <h5 class="mb-0 fw-bold">
                <i class="fa-solid fa-print me-2"></i>Configuración de Impresora
            </h5>
        </div>
    </div>

    <div class="module-scroll-area p-2">
        <div class="row justify-content-center">
            <div class="col-lg-7 col-md-10">

                {{-- ── Modo de impresión ───────────────────────────────────── --}}
                <div class="card mb-3">
                    <div class="card-header py-2">
                        <h6 class="mb-0 fw-semibold">
                            <i class="fa-solid fa-sliders me-1"></i> Modo de impresión
                        </h6>
                    </div>
                    <div class="card-body">

                        <div class="row g-3">

                            {{-- Opción: Navegador --}}
                            <div class="col-12 col-md-4">
                                <label class="d-block h-100 cursor-pointer"
                                       style="cursor:pointer;"
                                       wire:click="$set('printer_modo','browser')">
                                    <div class="card h-100 mb-0 border-2 {{ $printer_modo === 'browser' ? 'border-primary bg-primary bg-opacity-10' : 'border-secondary' }}"
                                         style="transition: all .2s;">
                                        <div class="card-body text-center py-3 px-2">
                                            <i class="fa-solid fa-globe fa-2x mb-2
                                               {{ $printer_modo === 'browser' ? 'text-primary' : 'text-muted' }}"></i>
                                            <p class="mb-1 fw-semibold small">Navegador</p>
                                            <p class="mb-0 text-muted" style="font-size:.72rem;">
                                                Abre ventana window.print() del navegador
                                            </p>
                                        </div>
                                    </div>
                                </label>
                            </div>

                            {{-- Opción: Módulo agente --}}
                            <div class="col-12 col-md-4">
                                <label class="d-block h-100 cursor-pointer"
                                       style="cursor:pointer;"
                                       wire:click="$set('printer_modo','escpos')">
                                    <div class="card h-100 mb-0 border-2 {{ $printer_modo === 'escpos' ? 'border-warning bg-warning bg-opacity-10' : 'border-secondary' }}"
                                         style="transition: all .2s;">
                                        <div class="card-body text-center py-3 px-2">
                                            <i class="fa-solid fa-desktop fa-2x mb-2
                                               {{ $printer_modo === 'escpos' ? 'text-warning' : 'text-muted' }}"></i>
                                            <p class="mb-1 fw-semibold small">Módulo PC</p>
                                            <p class="mb-0 text-muted" style="font-size:.72rem;">
                                                Agente printPOS instalado en Windows
                                            </p>
                                        </div>
                                    </div>
                                </label>
                            </div>

                            {{-- Opción: Red LAN / Android --}}
                            <div class="col-12 col-md-4">
                                <label class="d-block h-100 cursor-pointer"
                                       style="cursor:pointer;"
                                       wire:click="$set('printer_modo','network_ip')">
                                    <div class="card h-100 mb-0 border-2 {{ $printer_modo === 'network_ip' ? 'border-success bg-success bg-opacity-10' : 'border-secondary' }}"
                                         style="transition: all .2s;">
                                        <div class="card-body text-center py-3 px-2">
                                            <i class="fa-solid fa-network-wired fa-2x mb-2
                                               {{ $printer_modo === 'network_ip' ? 'text-success' : 'text-muted' }}"></i>
                                            <p class="mb-1 fw-semibold small">Red LAN / IP</p>
                                            <p class="mb-0 text-muted" style="font-size:.72rem;">
                                                Impresora en red local (PC o Android)
                                            </p>
                                        </div>
                                    </div>
                                </label>
                            </div>

                        </div>

                        {{-- Badge descriptivo del modo seleccionado --}}
                        <div class="mt-3">
                            @if($printer_modo === 'browser')
                                <div class="alert alert-secondary py-2 mb-0" style="font-size:.82rem;">
                                    <i class="fa-solid fa-circle-info me-1"></i>
                                    El navegador abrirá el diálogo de impresión del sistema. Compatible con cualquier dispositivo pero requiere interacción manual.
                                </div>
                            @elseif($printer_modo === 'escpos')
                                <div class="alert alert-warning py-2 mb-0" style="font-size:.82rem;">
                                    <i class="fa-solid fa-circle-info me-1"></i>
                                    Requiere el agente <strong>printPOS.exe</strong> instalado y activo en la PC cajera. No compatible con Android.
                                    Los nombres de impresora se configuran en el agente.
                                </div>
                            @else
                                <div class="alert alert-success py-2 mb-0" style="font-size:.82rem;">
                                    <i class="fa-solid fa-circle-info me-1"></i>
                                    El servidor envía los bytes ESC/POS directamente por TCP al IP de la impresora.
                                    <strong>Funciona desde cualquier dispositivo</strong> (PC, Android, tablet) sin agente local.
                                    La impresora debe estar conectada a la red local.
                                </div>
                            @endif
                        </div>

                    </div>
                </div>

                {{-- ── Configuración IP (solo si modo = network_ip) ─────── --}}
                @if($printer_modo === 'network_ip')
                <div class="card mb-3">
                    <div class="card-header py-2">
                        <h6 class="mb-0 fw-semibold">
                            <i class="fa-solid fa-ticket me-1"></i> Impresora de Tickets
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-2 align-items-end">
                            <div class="col-8">
                                <label class="form-label small mb-1">IP de la impresora</label>
                                <input type="text"
                                       class="form-control form-control-sm @error('printer_ip') is-invalid @enderror"
                                       wire:model.blur="printer_ip"
                                       placeholder="Ej: 192.168.1.100">
                                @error('printer_ip')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-4">
                                <label class="form-label small mb-1">Puerto TCP</label>
                                <input type="number"
                                       class="form-control form-control-sm"
                                       wire:model.blur="printer_puerto"
                                       min="1" max="65535">
                            </div>
                            <div class="col-12 pt-0">
                                <button class="btn btn-sm btn-outline-success"
                                        wire:click="testConexionTicket"
                                        wire:loading.attr="disabled">
                                    <span wire:loading wire:target="testConexionTicket"
                                          class="spinner-border spinner-border-sm me-1"></span>
                                    <i class="fa-solid fa-plug-circle-check me-1"
                                       wire:loading.class.remove="fa-plug-circle-check"
                                       wire:target="testConexionTicket"></i>
                                    Probar conexión
                                </button>
                                @if($testTicketOk === true)
                                    <span class="text-success ms-2 small fw-semibold">
                                        <i class="fa-solid fa-check"></i> Conectado
                                    </span>
                                @elseif($testTicketOk === false)
                                    <span class="text-danger ms-2 small fw-semibold">
                                        <i class="fa-solid fa-xmark"></i> Sin respuesta
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header py-2">
                        <h6 class="mb-0 fw-semibold">
                            <i class="fa-solid fa-utensils me-1"></i>
                            Impresora de Cocina
                            <span class="text-muted fw-normal" style="font-size:.78rem;">(opcional)</span>
                        </h6>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-2">
                            Si está vacía, las comandas se envían a la misma impresora de tickets.
                        </p>
                        <div class="row g-2 align-items-end">
                            <div class="col-8">
                                <label class="form-label small mb-1">IP impresora cocina</label>
                                <input type="text"
                                       class="form-control form-control-sm @error('printer_ip_cocina') is-invalid @enderror"
                                       wire:model.blur="printer_ip_cocina"
                                       placeholder="Ej: 192.168.1.101">
                                @error('printer_ip_cocina')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-4">
                                <label class="form-label small mb-1">Puerto TCP</label>
                                <input type="number"
                                       class="form-control form-control-sm"
                                       wire:model.blur="printer_puerto_cocina"
                                       min="1" max="65535">
                            </div>
                            <div class="col-12 pt-0">
                                <button class="btn btn-sm btn-outline-success"
                                        wire:click="testConexionCocina"
                                        wire:loading.attr="disabled"
                                        @if(empty(trim($printer_ip_cocina))) disabled @endif>
                                    <span wire:loading wire:target="testConexionCocina"
                                          class="spinner-border spinner-border-sm me-1"></span>
                                    <i class="fa-solid fa-plug-circle-check me-1"></i>
                                    Probar conexión
                                </button>
                                @if($testCocinaOk === true)
                                    <span class="text-success ms-2 small fw-semibold">
                                        <i class="fa-solid fa-check"></i> Conectado
                                    </span>
                                @elseif($testCocinaOk === false)
                                    <span class="text-danger ms-2 small fw-semibold">
                                        <i class="fa-solid fa-xmark"></i> Sin respuesta
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                {{-- ── Botón guardar ───────────────────────────────────────── --}}
                <div class="d-flex justify-content-end mb-3">
                    <button class="btn btn-primary px-4"
                            wire:click="guardar"
                            wire:loading.attr="disabled">
                        <span wire:loading wire:target="guardar"
                              class="spinner-border spinner-border-sm me-1"></span>
                        <i class="fa-solid fa-floppy-disk me-1"
                           wire:loading.class.remove="fa-floppy-disk"
                           wire:target="guardar"></i>
                        Guardar configuración
                    </button>
                </div>

            </div>
        </div>
    </div>

</div>
