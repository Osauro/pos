<div>
    <div class="container-fluid" style="padding-top: 0 !important;">
        <div class="row starter-main" style="margin-top: 0 !important;">
            <div class="col-sm-12" style="padding-top: 0 !important;">
                <div class="card" style="margin-top: 0 !important;">

                    <div class="card-header card-no-border pb-2">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div>
                                <h4 class="mb-0 fw-bold">
                                    <i class="fa-solid fa-money-bill-wave me-2"></i>Pagos de Suscripción
                                </h4>
                                @if($totalPendientes > 0)
                                    <small class="text-danger fw-semibold">
                                        <i class="fa-solid fa-circle-exclamation me-1"></i>
                                        {{ $totalPendientes }} pendiente(s) de verificación
                                    </small>
                                @endif
                            </div>
                            <div class="d-flex gap-2 align-items-center flex-wrap">
                                <div class="input-group" style="max-width:240px;">
                                    <span class="input-group-text"><i class="fa-solid fa-search"></i></span>
                                    <input type="text" class="form-control" placeholder="Buscar negocio..."
                                           wire:model.live="search">
                                </div>
                                <div class="form-check form-switch mb-0 d-flex align-items-center gap-2">
                                    <input class="form-check-input" type="checkbox" id="soloPendientes"
                                           wire:model.live="soloPendientes">
                                    <label class="form-check-label small" for="soloPendientes">Solo pendientes</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-body pt-2 px-2 pb-3">
                        @if($pagos->isEmpty())
                        <div class="text-center py-5">
                            <i class="fa-solid fa-check-circle fa-4x text-success opacity-50 mb-3"></i>
                            <p class="h5 text-muted mb-0">
                                {{ $soloPendientes ? 'No hay pagos pendientes' : 'No se encontraron pagos' }}
                            </p>
                        </div>
                        @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle" style="font-size:0.85rem;">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Negocio</th>
                                        <th>Monto</th>
                                        <th>Estado</th>
                                        <th>Enviado</th>
                                        <th>Notas</th>
                                        <th>Verificado por</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pagos as $pago)
                                    <tr>
                                        <td class="text-muted">#{{ $pago->id }}</td>
                                        <td>
                                            <div class="fw-semibold">{{ $pago->tenant->nombre }}</div>
                                            <small class="text-muted">ID: {{ $pago->tenant_id }}</small>
                                        </td>
                                        <td><span class="badge bg-success">{{ number_format($pago->monto, 0) }} Bs</span></td>
                                        <td>
                                            @if($pago->estado === 'pendiente')
                                                <span class="badge bg-warning text-dark">Pendiente</span>
                                            @elseif($pago->estado === 'verificado')
                                                <span class="badge bg-success">Verificado</span>
                                            @else
                                                <span class="badge bg-danger">Rechazado</span>
                                            @endif
                                        </td>
                                        <td>
                                            {{ $pago->created_at->format('d/m/Y') }}<br>
                                            <small class="text-muted">{{ $pago->created_at->format('H:i') }}</small>
                                        </td>
                                        <td><small class="text-muted">{{ $pago->notas ?? '—' }}</small></td>
                                        <td>
                                            @if($pago->verificadoPor)
                                                <small>{{ $pago->verificadoPor->nombre }}</small><br>
                                                <small class="text-muted">{{ $pago->verificado_at?->format('d/m/Y') }}</small>
                                            @else
                                                <small class="text-muted">—</small>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($pago->estado === 'pendiente')
                                            <div class="d-flex gap-1 justify-content-center">
                                                <button class="btn btn-sm btn-outline-primary"
                                                        wire:click="verPago({{ $pago->id }})"
                                                        title="Ver comprobante">
                                                    <i class="fa-solid fa-eye"></i>
                                                </button>
                                                <button class="btn btn-sm btn-success"
                                                        wire:click="confirmarPago({{ $pago->id }})"
                                                        wire:confirm="¿Confirmar pago y renovar suscripción por 1 año?"
                                                        title="Confirmar">
                                                    <i class="fa-solid fa-check"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger"
                                                        wire:click="verPago({{ $pago->id }})"
                                                        title="Rechazar">
                                                    <i class="fa-solid fa-times"></i>
                                                </button>
                                            </div>
                                            @else
                                            <button class="btn btn-sm btn-outline-secondary"
                                                    wire:click="verPago({{ $pago->id }})"
                                                    title="Ver detalles">
                                                <i class="fa-solid fa-eye"></i>
                                            </button>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3">{{ $pagos->links() }}</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL: Ver comprobante --}}
    @if($isOpenPago && $pagoId)
    @php $pagoModal = \App\Models\PagoSuscripcion::with('tenant')->find($pagoId); @endphp
    @if($pagoModal)
    <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,.5); z-index:1060;">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">
                        <i class="fa-solid fa-receipt me-2"></i>
                        Comprobante — {{ $pagoModal->tenant->nombre }}
                    </h5>
                    <button type="button" class="btn-close" wire:click="$set('isOpenPago', false)"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            @if($pagoModal->comprobante_path)
                                @php $ext = strtolower(pathinfo($pagoModal->comprobante_path, PATHINFO_EXTENSION)); @endphp
                                @if(in_array($ext, ['jpg','jpeg','png']))
                                    <img src="{{ asset('storage/'.$pagoModal->comprobante_path) }}"
                                         alt="Comprobante" class="img-fluid rounded border">
                                @elseif($ext === 'pdf')
                                    <a href="{{ asset('storage/'.$pagoModal->comprobante_path) }}"
                                       target="_blank" class="btn btn-outline-primary w-100">
                                        <i class="fa-solid fa-file-pdf me-1"></i>Abrir PDF
                                    </a>
                                @endif
                            @else
                                <p class="text-muted text-center py-4">Sin archivo adjunto.</p>
                            @endif
                        </div>
                        <div class="col-12 col-md-6">
                            <table class="table table-borderless table-sm mb-3" style="font-size:0.85rem;">
                                <tr><td class="text-muted">Negocio</td><td class="fw-semibold">{{ $pagoModal->tenant->nombre }}</td></tr>
                                <tr><td class="text-muted">Monto</td><td>{{ number_format($pagoModal->monto, 0) }} Bs</td></tr>
                                <tr><td class="text-muted">Estado</td>
                                    <td>
                                        @if($pagoModal->estado === 'pendiente')
                                            <span class="badge bg-warning text-dark">Pendiente</span>
                                        @elseif($pagoModal->estado === 'verificado')
                                            <span class="badge bg-success">Verificado</span>
                                        @else
                                            <span class="badge bg-danger">Rechazado</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr><td class="text-muted">Enviado</td><td>{{ $pagoModal->created_at->format('d/m/Y H:i') }}</td></tr>
                                <tr><td class="text-muted">Notas</td><td>{{ $pagoModal->notas ?? '—' }}</td></tr>
                                @if($pagoModal->notas_verificacion)
                                <tr><td class="text-muted">Mot. rechazo</td><td class="text-danger">{{ $pagoModal->notas_verificacion }}</td></tr>
                                @endif
                            </table>

                            @if($pagoModal->estado === 'pendiente')
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Motivo de rechazo (opcional)</label>
                                <textarea wire:model="notasRechazo" class="form-control form-control-sm" rows="2"
                                          placeholder="Ej: comprobante ilegible, monto incorrecto..."></textarea>
                            </div>
                            <div class="d-flex gap-2">
                                <button class="btn btn-success flex-fill"
                                        wire:click="confirmarPago({{ $pagoModal->id }})"
                                        wire:confirm="¿Confirmar pago y renovar suscripción por 1 año?">
                                    <i class="fa-solid fa-check me-1"></i>Confirmar
                                </button>
                                <button class="btn btn-outline-danger flex-fill"
                                        wire:click="rechazarPago({{ $pagoModal->id }})">
                                    <i class="fa-solid fa-times me-1"></i>Rechazar
                                </button>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
    @endif
</div>
