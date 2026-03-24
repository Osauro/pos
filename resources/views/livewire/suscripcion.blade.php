<div>
    <div class="container-fluid" style="padding-top: 0 !important;">
        <div class="row starter-main" style="margin-top: 0 !important;">
            <div class="col-sm-12" style="padding-top: 0 !important;">
                <div class="card" style="margin-top: 0 !important;">
                    <div class="card-header card-no-border pb-2">
                        <h3 class="mb-0">
                            <i class="fa-solid fa-crown me-2"></i>Mi Suscripción
                        </h3>
                        <small class="text-muted">Estado de tu plan y fecha de vencimiento</small>
                    </div>

                    @if($tenant)
                    <div class="card-body pt-2">

                        {{-- Banner cuenta inactiva / suspendida --}}
                        @if(! $tenant->isActivo())
                        <div class="border border-danger rounded-3 mb-4 overflow-hidden">

                            {{-- Cabecera --}}
                            <div class="bg-danger text-white px-4 py-3 d-flex align-items-center gap-3">
                                <i class="fa-solid fa-lock fa-2x"></i>
                                <div>
                                    <h5 class="mb-0 fw-bold">Cuenta suspendida</h5>
                                    <small>
                                        @if($tenant->status !== 'activo')
                                            Estado: <strong>{{ ucfirst($tenant->status) }}</strong> —
                                        @endif
                                        Realiza el pago de <strong>{{ $precioAnual }} Bs/año</strong> para reactivarla.
                                    </small>
                                </div>
                            </div>

                            <div class="p-4">
                                <div class="row g-4">

                                    {{-- Col izq: QR de pago --}}
                                    <div class="col-12 col-md-4 text-center">
                                        <p class="fw-semibold mb-2">
                                            <i class="fa-solid fa-qrcode me-1 text-danger"></i>QR de pago
                                        </p>
                                        @php $qrUrl = env('PAYMENT_QR_URL', ''); @endphp
                                        @if($qrUrl)
                                            <div class="border rounded p-2 d-inline-block bg-white mb-2">
                                                <img src="{{ $qrUrl }}" alt="QR de pago" width="180" height="180" style="display:block; object-fit:contain;">
                                            </div>
                                        @else
                                            <div class="border rounded p-2 d-inline-block bg-white mb-2">
                                                <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data={{ urlencode('https://wa.me/59173010688?text=Hola, necesito renovar mi suscripción TPV - '.$tenant->nombre) }}"
                                                     alt="QR de contacto" width="180" height="180" style="display:block;">
                                            </div>
                                        @endif
                                        <small class="text-muted d-block">
                                            {{ $qrUrl ? 'Escanea para realizar el pago' : 'Escanea para contactar al soporte' }}
                                        </small>
                                        <a href="https://wa.me/59173010688?text={{ urlencode('Hola, necesito renovar mi suscripción del sistema TPV - Negocio: '.$tenant->nombre) }}"
                                           target="_blank"
                                           class="btn btn-success btn-sm mt-2 d-inline-flex align-items-center gap-1">
                                            <i class="fa-brands fa-whatsapp"></i> WhatsApp
                                        </a>
                                    </div>

                                    {{-- Col der: instrucciones + formulario comprobante --}}
                                    <div class="col-12 col-md-8">

                                        <p class="fw-semibold mb-2">Pasos para reactivar:</p>
                                        <ol class="text-muted mb-3" style="font-size:0.9rem; padding-left:1.2rem;">
                                            <li>Escanea el QR y realiza el pago de <strong>{{ $precioAnual }} Bs</strong></li>
                                            <li>Toma una foto o captura del comprobante</li>
                                            <li>Súbelo aquí abajo y pulsa <em>Enviar comprobante</em></li>
                                            <li>El administrador verificará y reactivará tu cuenta</li>
                                        </ol>

                                        @if($pagoPendiente)
                                        {{-- Ya hay un comprobante pendiente --}}
                                        <div class="alert alert-warning d-flex align-items-center gap-2 py-2 mb-0">
                                            <i class="fa-solid fa-clock fa-lg"></i>
                                            <div>
                                                <strong>Comprobante en revisión</strong><br>
                                                <small>
                                                    Enviado el {{ $pagoPendiente->created_at->format('d/m/Y H:i') }}.
                                                    El administrador lo verificará pronto.
                                                </small>
                                            </div>
                                        </div>
                                        @else
                                        {{-- Formulario de subida --}}
                                        <div class="border rounded p-3 bg-light">
                                            <p class="fw-semibold mb-2 small">
                                                <i class="fa-solid fa-upload me-1 text-primary"></i>Subir comprobante de pago
                                            </p>

                                            <div class="mb-2">
                                                <input type="file"
                                                       wire:model="comprobante"
                                                       accept=".jpg,.jpeg,.png,.pdf"
                                                       class="form-control form-control-sm @error('comprobante') is-invalid @enderror">
                                                @error('comprobante')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                                <small class="text-muted">JPG, PNG o PDF · máx 5 MB</small>
                                            </div>

                                            {{-- Preview imagen --}}
                                            @if($comprobante && in_array(strtolower($comprobante->getClientOriginalExtension()), ['jpg','jpeg','png']))
                                            <div class="mb-2">
                                                <img src="{{ $comprobante->temporaryUrl() }}"
                                                     alt="Vista previa" class="img-thumbnail"
                                                     style="max-height:120px;">
                                            </div>
                                            @endif

                                            <div class="mb-2">
                                                <textarea wire:model="notasPago"
                                                          class="form-control form-control-sm"
                                                          rows="2"
                                                          placeholder="Notas opcionales (número de transacción, banco, etc.)"></textarea>
                                            </div>

                                            <button wire:click="enviarComprobante"
                                                    wire:loading.attr="disabled"
                                                    class="btn btn-primary btn-sm w-100">
                                                <span wire:loading wire:target="enviarComprobante"
                                                      class="spinner-border spinner-border-sm me-1"></span>
                                                <i wire:loading.remove wire:target="enviarComprobante"
                                                   class="fa-solid fa-paper-plane me-1"></i>
                                                Enviar comprobante
                                            </button>
                                        </div>
                                        @endif

                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        {{-- Alerta según estado --}}
                        @if($diasRestantes !== null)
                            @if($diasRestantes < 0)
                                <div class="alert alert-danger d-flex align-items-center gap-2 py-2 mb-3">
                                    <i class="fa-solid fa-circle-exclamation fa-lg"></i>
                                    <span>
                                        @if($esTrial)
                                            Tu <strong>mes de prueba gratuita</strong> ha vencido.
                                        @else
                                            Tu <strong>suscripción anual</strong> ha vencido.
                                        @endif
                                        Contacta al administrador para continuar usando el sistema.
                                    </span>
                                </div>
                            @elseif($diasRestantes <= 15)
                                <div class="alert alert-warning d-flex align-items-center gap-2 py-2 mb-3">
                                    <i class="fa-solid fa-triangle-exclamation fa-lg"></i>
                                    <span>
                                        @if($esTrial)
                                            Tu prueba gratuita vence en <strong>{{ $diasRestantes }} días</strong>.
                                            ¡Suscríbete por <strong>{{ $precioAnual }} Bs/año</strong> para continuar!
                                        @else
                                            Tu suscripción vence en <strong>{{ $diasRestantes }} días</strong>. ¡Renueva pronto!
                                        @endif
                                    </span>
                                </div>
                            @endif
                        @endif

                        {{-- Tarjetas de resumen --}}
                        <div class="row g-3 mb-4">
                            <div class="col-6 col-lg-3">
                                <div class="card h-100 border-0 shadow-sm text-center p-3">
                                    <div class="mb-2">
                                        <span class="badge bg-{{ $tenant->status === 'activo' ? 'success' : ($tenant->status === 'suspendido' ? 'danger' : 'secondary') }} px-3 py-2" style="font-size:0.85rem;">
                                            {{ ucfirst($tenant->status) }}
                                        </span>
                                    </div>
                                    <small class="text-muted">Estado</small>
                                </div>
                            </div>

                            <div class="col-6 col-lg-3">
                                <div class="card h-100 border-0 shadow-sm text-center p-3">
                                    <div class="mb-2">
                                        <span class="badge bg-{{ $badgeColor }} px-3 py-2" style="font-size:0.85rem;">
                                            <i class="fa-solid fa-{{ $esTrial ? 'clock' : 'crown' }} me-1"></i>
                                            {{ $esTrial ? 'Prueba' : 'Anual' }}
                                        </span>
                                    </div>
                                    <small class="text-muted">Plan</small>
                                </div>
                            </div>

                            <div class="col-6 col-lg-3">
                                <div class="card h-100 border-0 shadow-sm text-center p-3">
                                    <div class="fw-bold mb-1" style="font-size:1.2rem; color:var(--theme-default);">
                                        @if($tenant->bill_date)
                                            {{ $tenant->bill_date->format('d/m/Y') }}
                                        @else —
                                        @endif
                                    </div>
                                    <small class="text-muted">Vencimiento</small>
                                </div>
                            </div>

                            <div class="col-6 col-lg-3">
                                <div class="card h-100 border-0 shadow-sm text-center p-3">
                                    <div class="fw-bold mb-1" style="font-size:1.4rem;">
                                        @if($diasRestantes !== null)
                                            <span class="text-{{ $diasRestantes < 0 ? 'danger' : ($diasRestantes <= 7 ? 'warning' : 'success') }}">
                                                {{ $diasRestantes < 0 ? abs($diasRestantes).'d vencido' : $diasRestantes.'d' }}
                                            </span>
                                        @else —
                                        @endif
                                    </div>
                                    <small class="text-muted">Días restantes</small>
                                </div>
                            </div>
                        </div>

                        {{-- Detalle + Contacto --}}
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <div class="card border-0 shadow-sm">
                                    <div class="card-header bg-light py-2">
                                        <h6 class="mb-0 fw-bold">
                                            <i class="fa-solid fa-store me-2 text-primary"></i>Datos del negocio
                                        </h6>
                                    </div>
                                    <div class="card-body py-3">
                                        <table class="table table-borderless mb-0" style="font-size:0.9rem;">
                                            <tr>
                                                <td class="text-muted ps-0" style="width:35%;">Nombre</td>
                                                <td class="fw-semibold">{{ $tenant->nombre }}</td>
                                            </tr>
                                            @if($tenant->telefono)
                                            <tr>
                                                <td class="text-muted ps-0">Teléfono</td>
                                                <td>{{ $tenant->telefono }}</td>
                                            </tr>
                                            @endif
                                            @if($tenant->direccion)
                                            <tr>
                                                <td class="text-muted ps-0">Dirección</td>
                                                <td>{{ $tenant->direccion }}</td>
                                            </tr>
                                            @endif
                                            <tr>
                                                <td class="text-muted ps-0">ID</td>
                                                <td><code>#{{ $tenant->id }}</code></td>
                                            </tr>
                                            <tr>
                                                <td class="text-muted ps-0">Registro</td>
                                                <td>{{ $tenant->created_at->format('d/m/Y') }}</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <div class="card border-0 shadow-sm">
                                    <div class="card-header bg-light py-2">
                                        <h6 class="mb-0 fw-bold">
                                            <i class="fa-solid fa-receipt me-2 text-primary"></i>Plan de suscripción
                                        </h6>
                                    </div>
                                    <div class="card-body py-3">
                                        {{-- Resumen del plan --}}
                                        <div class="d-flex justify-content-between align-items-center border rounded p-3 mb-3">
                                            <div>
                                                <div class="fw-bold">Plan Anual</div>
                                                <small class="text-muted">Acceso completo al sistema TPV</small>
                                            </div>
                                            <div class="text-end">
                                                <div class="fw-bold text-success" style="font-size:1.3rem;">{{ $precioAnual }} Bs</div>
                                                <small class="text-muted">por año</small>
                                            </div>
                                        </div>

                                        @if($esTrial && $diasRestantes !== null && $diasRestantes >= 0)
                                        <div class="alert alert-success py-2 mb-3" style="font-size:0.85rem;">
                                            <i class="fa-solid fa-gift me-1"></i>
                                            Estás en el <strong>mes de prueba gratuita</strong>.
                                            Te quedan <strong>{{ $diasRestantes }} días</strong>.
                                        </div>
                                        @endif

                                        <p class="text-muted mb-3" style="font-size:0.85rem;">
                                            Para renovar tu suscripción, contacta al administrador y realiza el pago de <strong>{{ $precioAnual }} Bs</strong>.
                                        </p>

                                        <div class="d-flex flex-column gap-2">
                                            <a href="https://wa.me/59173010688"
                                               target="_blank"
                                               class="btn btn-success btn-sm d-flex align-items-center gap-2 justify-content-center">
                                                <i class="fa-brands fa-whatsapp" style="font-size:16px;"></i>
                                                Contactar por WhatsApp
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                    @else
                    <div class="card-body text-center py-5">
                        <i class="fa-solid fa-store-slash fa-5x text-muted opacity-25 mb-3"></i>
                        <p class="h5 text-muted">No hay negocio activo en sesión.</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
