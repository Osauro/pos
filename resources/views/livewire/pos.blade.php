<div>
    {{-- Barra de título y categorías --}}
    <div class="module-sticky-header">
        <div class="d-flex justify-content-between align-items-center gap-2">
            <h5 class="mb-0 fw-bold d-none d-lg-block flex-shrink-0">
                <i class="fa-solid fa-cash-register me-2 text-primary"></i>Punto de Venta
            </h5>
            <div class="btn-group pos-cat-group" role="group">
                @foreach([['Platos','fa-utensils'], ['Refrescos','fa-glass-water'], ['Porciones','fa-bowl-food']] as [$cat, $ico])
                    <button type="button"
                            wire:click="setTipoFiltro('{{ $cat }}')"
                            class="btn {{ $tipo_filtro === $cat ? 'btn-primary' : 'btn-outline-primary' }}">
                        <i class="fa-solid {{ $ico }}"></i><span class="d-none d-sm-inline ms-1">{{ $cat }}</span>
                    </button>
                @endforeach
            </div>
            <div class="btn-group flex-shrink-0" role="group">
                <button wire:click="toggleAutoComanda"
                        title="{{ $auto_comanda ? 'Comanda ON' : 'Comanda OFF' }}"
                        class="btn btn-sm {{ $auto_comanda ? 'btn-success' : 'btn-outline-secondary' }}">
                    <i class="fa-solid fa-kitchen-set"></i>
                    <span class="d-none d-lg-inline ms-1">Comanda</span>
                </button>
                <button wire:click="toggleAutoTicket"
                        title="{{ $auto_ticket ? 'Ticket ON' : 'Ticket OFF' }}"
                        class="btn btn-sm {{ $auto_ticket ? 'btn-success' : 'btn-outline-secondary' }}">
                    <i class="fa-solid fa-receipt"></i>
                    <span class="d-none d-lg-inline ms-1">Ticket</span>
                </button>
                <button type="button"
                        wire:click="setOrdenProductos('{{ $orden_productos === 'popularidad' ? 'nombre' : 'popularidad' }}')"
                        class="btn btn-sm {{ $orden_productos === 'popularidad' ? 'btn-warning' : 'btn-secondary' }}"
                        title="{{ $orden_productos === 'popularidad' ? 'Ordenar por nombre' : 'Ordenar por popularidad' }}">
                    @if($orden_productos === 'popularidad')
                        <i class="fa-solid fa-fire"></i>
                        <span class="d-none d-lg-inline ms-1">Popular</span>
                    @else
                        <i class="fa-solid fa-arrow-down-a-z"></i>
                        <span class="d-none d-lg-inline ms-1">A-Z</span>
                    @endif
                </button>
                <button type="button"
                        wire:click="toggleHayFideo"
                        class="btn btn-sm {{ $hay_fideo ? 'btn-success' : 'btn-outline-danger' }}"
                        title="{{ $hay_fideo ? 'Hay fideo — clic para indicar que no hay' : 'Sin fideo — clic para activar fideo' }}">
                    🍝
                    <span class="d-none d-lg-inline ms-1">{{ $hay_fideo ? 'Fideo' : 'Sin fideo' }}</span>
                </button>
            </div>
        </div>
    </div>

    <style>
        .bottom-nav        { display: none !important; }
        .page-body         { padding: 0 !important; overflow: hidden !important; }
        .page-body-wrapper { overflow: hidden !important; }
        html, body         { overflow: hidden !important; height: 100% !important; }
        @media (max-width: 991.98px) {
            .pos-cat-group { width: 100%; }
            .pos-cat-group .btn { flex: 1 1 auto; }
        }
    </style>

    {{-- ══════════════════════════════════════════
         LAYOUT PRINCIPAL: col-9 catálogo / col-3 carrito
    ══════════════════════════════════════════ --}}
    <div class="row g-0 pos-layout">

        {{-- ══ PANEL CATÁLOGO (col-md-9) ══ --}}
        <div class="col-12 col-lg-9 pos-catalog {{ $mostrar_carrito ? 'd-none d-lg-block' : '' }}">

            {{-- Grid de productos --}}
            <div class="pos-products-wrap p-2 bg-light">
                <div class="row g-2">
                    @forelse($productos as $producto)
                        @php
                            $ico = match($producto->tipo) {
                                'Refrescos' => 'fa-glass-water',
                                'Porciones' => 'fa-bowl-food',
                                default     => 'fa-utensils'
                            };
                            $cantidadEnCarrito = collect($carrito)
                                ->where('producto_id', $producto->id)
                                ->sum('cantidad');
                            $enCarrito = $cantidadEnCarrito > 0;
                        @endphp
                        <div class="col-6 col-md-4 col-lg-3 col-xl-3">
                            <div class="card prod-card h-100 {{ $enCarrito ? 'prod-card--active' : '' }}"
                                 wire:click="agregarAlCarrito({{ $producto->id }})"
                                 wire:loading.class="opacity-50"
                                 wire:target="agregarAlCarrito({{ $producto->id }})">

                                <div class="prod-card__img-wrap">
                                @if($producto->imagen)
                                    <img src="{{ asset('storage/' . $producto->imagen) }}"
                                         class="prod-card__img"
                                         alt="{{ $producto->nombre }}">
                                @else
                                    <div class="prod-card__placeholder d-flex align-items-center justify-content-center">
                                        <i class="fa-solid {{ $ico }} fa-2x text-primary opacity-50"></i>
                                    </div>
                                @endif
                                    <span class="prod-card__precio-overlay">Bs. {{ number_format($producto->precio, 2) }}</span>
                                </div>

                                @if($enCarrito)
                                    <span class="prod-card__badge">{{ $cantidadEnCarrito }}</span>
                                @endif

                                <div class="card-body p-2 text-center">
                                    <p class="prod-card__nombre mb-0">{{ $producto->nombre }}</p>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12 text-center text-muted py-5">
                            <i class="fa-solid fa-plate-wheat fa-3x d-block mb-3 opacity-25"></i>
                            <p class="mb-0">Sin productos disponibles en esta categoría</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- ══ PANEL CARRITO (col-md-3) ══ --}}
        <div class="col-12 col-lg-3 pos-cart border-start {{ !$mostrar_carrito ? 'd-none d-lg-block' : '' }}">

            {{-- Header carrito --}}
            <div class="pos-cart-header px-3 py-2 bg-white border-bottom">
                <div class="d-flex align-items-center justify-content-between">
                    <span class="fw-bold">
                        <i class="fa-solid fa-cart-shopping text-primary me-1"></i> Pedido
                    </span>
                    @php
                        $totalPlatos    = collect($carrito)->where('categoria','Platos')->sum('cantidad');
                        $totalRefrescos = collect($carrito)->where('categoria','Refrescos')->sum('cantidad');
                        $totalPorciones = collect($carrito)->where('categoria','Porciones')->sum('cantidad');
                    @endphp
                    <div class="d-flex align-items-center gap-2">

                        @if($totalPlatos > 0)
                            <span class="badge rounded-pill px-3 py-2" style="background:#7c4b2a;font-size:.82rem">
                                <i class="fa-solid fa-utensils me-1"></i>{{ $totalPlatos }}
                            </span>
                        @endif
                        @if($totalRefrescos > 0)
                            <span class="badge rounded-pill px-3 py-2 bg-info text-white" style="font-size:.82rem">
                                <i class="fa-solid fa-glass-water me-1"></i>{{ $totalRefrescos }}
                            </span>
                        @endif
                        @if($totalPorciones > 0)
                            <span class="badge rounded-pill px-3 py-2 bg-success" style="font-size:.82rem">
                                <i class="fa-solid fa-bowl-food me-1"></i>{{ $totalPorciones }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Lista de items --}}
            <div class="pos-cart-body p-2 bg-light">
                @forelse($carrito as $key => $item)
                    <div class="card border-0 shadow-sm mb-2">
                        <div class="card-body p-2">
                            <div class="d-flex align-items-stretch gap-2">

                                {{-- Columna imagen --}}
                                @if($item['imagen'])
                                    <img src="{{ asset('storage/' . $item['imagen']) }}"
                                         class="rounded flex-shrink-0 align-self-center"
                                         style="width:56px;height:56px;object-fit:cover">
                                @else
                                    <div class="rounded flex-shrink-0 align-self-center bg-light border d-flex align-items-center justify-content-center"
                                         style="width:56px;height:56px">
                                        <i class="fa-solid fa-utensils text-muted"></i>
                                    </div>
                                @endif

                                {{-- Columna contenido --}}
                                <div class="flex-fill d-flex flex-column justify-content-center" style="min-width:0;gap:3px">

                                    {{-- Fila 1: cantidad - nombre | [X] --}}
                                    <div class="d-flex align-items-center justify-content-between gap-1 w-100">
                                        <div class="d-flex align-items-center gap-1 flex-fill" style="min-width:0">
                                            <span class="fw-bold flex-shrink-0" style="font-size:.85rem">{{ $item['cantidad'] }}</span>
                                            <span class="text-muted flex-shrink-0" style="font-size:.85rem">-</span>
                                            <span class="text-truncate" style="font-size:.85rem">{{ $item['nombre'] }}</span>
                                        </div>
                                        <button wire:click="eliminarDelCarrito('{{ $key }}')"
                                                class="btn btn-outline-danger btn-sm flex-shrink-0 py-0 px-1" style="line-height:1.4">
                                            <i class="fa-solid fa-xmark" style="font-size:.7rem"></i>
                                        </button>
                                    </div>

                                    {{-- Fila 2: detalles | subtotal --}}
                                    <div class="d-flex align-items-center justify-content-between gap-1 w-100">
                                        <div class="d-flex align-items-center gap-2">
                                            @if($item['tipo'] === 'plato')
                                                @foreach(['arroz' => 'A', 'fideo' => 'F', 'mixto' => 'M'] as $det => $etiqueta)
                                                    @if(($item['acomp'][$det] ?? 0) > 0)
                                                    <div class="d-flex align-items-center gap-1 flex-shrink-0">
                                                        <span class="text-muted" style="font-size:.75rem">{{ $etiqueta }}:</span>
                                                        <input type="number" min="0"
                                                               class="form-control form-control-sm text-center px-0"
                                                               style="width:36px;height:22px;font-size:.78rem"
                                                               value="{{ $item['acomp'][$det] }}"
                                                               x-on:focus="$event.target.select()"
                                                               x-on:change="$wire.actualizarAcompanamiento('{{ $key }}', '{{ $det }}', $event.target.value)">
                                                    </div>
                                                    @endif
                                                @endforeach
                                            @endif
                                        </div>
                                        <span class="fw-bold text-success flex-shrink-0" style="font-size:.82rem">Bs. {{ number_format($item['subtotal'], 2) }}</span>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div style="position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;" class="text-muted">
                        <div class="rounded-circle d-flex align-items-center justify-content-center mb-3"
                             style="width:72px;height:72px;background:rgba(115,102,255,.08)">
                            <i class="fa-solid fa-cart-shopping fa-2x" style="color:rgba(115,102,255,.35)"></i>
                        </div>
                        <p class="fw-semibold mb-1" style="font-size:.9rem;color:#6c757d">Pedido vacío</p>
                        <p class="mb-0" style="font-size:.78rem;color:#adb5bd">Toca un producto para añadirlo</p>
                    </div>
                @endforelse
            </div>

            {{-- Footer: botones (solo escritorio) --}}
            <div class="pos-cart-footer">
                <div class="px-3 py-3 d-flex gap-2">
                    <button wire:click="cancelarVenta"
                            class="btn btn-danger btn-lg px-3"
                            title="Cancelar venta"
                            {{ empty($carrito) ? 'disabled' : '' }}>
                        <i class="fa-solid fa-ban"></i>
                    </button>
                    <button @click="$dispatch('abrir-cobro', { total: {{ $total }} })"
                            wire:loading.attr="disabled"
                            wire:target="procesarVenta"
                            class="btn btn-success btn-lg fw-bold flex-fill"
                            {{ empty($carrito) ? 'disabled' : '' }}>
                        <i class="fa-solid fa-check me-2"></i>Bs. {{ number_format($total, 2) }}
                    </button>
                </div>
            </div>
        </div>

    </div>{{-- /row g-0 pos-layout --}}

    {{-- ══ BOTTOM BAR (solo móvil) ══ --}}
    <div class="pos-bottom-bar d-lg-none">
        <button wire:click="cancelarVenta"
                class="pos-act pos-act--danger"
                {{ empty($carrito) ? 'disabled' : '' }}>
            <i class="fa-solid fa-ban"></i>
            <span>Cancelar</span>
        </button>
        <button wire:click="toggleCarrito" class="pos-act pos-act--cart">
            <i class="fa-solid fa-{{ $mostrar_carrito ? 'utensils' : 'cart-shopping' }}"></i>
            <span>{{ $mostrar_carrito ? 'Menú' : 'Carrito' }}</span>
            @php $totalItems = collect($carrito)->sum('cantidad'); @endphp
            @if($totalItems > 0)
                <span class="pos-act__badge">{{ $totalItems }}</span>
            @endif
        </button>
        <button @click="$dispatch('abrir-cobro', { total: {{ $total }} })"
                wire:loading.attr="disabled"
                wire:target="procesarVenta"
                class="pos-act pos-act--pay"
                {{ empty($carrito) ? 'disabled' : '' }}>
            <i class="fa-solid fa-check"></i>
            <span>Bs. {{ number_format($total, 2) }}</span>
        </button>
    </div>

    {{-- ══ SELECTOR DE ACOMPAÑAMIENTO ══ --}}
    @if($mostrar_selector)
        @php $prodPendiente = $productos->firstWhere('id', $producto_pendiente_id); @endphp
        <div class="pos-selector-overlay" wire:click="cancelarSelector">
            <div class="pos-selector" wire:click.stop>
                <div class="pos-selector__handle"></div>
                <div class="pos-selector__nombre">{{ $prodPendiente->nombre ?? 'Producto' }}</div>
                <p class="pos-selector__sub">¿Con qué acompañamiento?</p>
                <div class="pos-selector__opciones">
                    <button wire:click="seleccionarAcompanamiento('arroz')" class="pos-sel-btn">
                        <span class="pos-sel-btn__icon">🍚</span>
                        <span class="pos-sel-btn__label">Arroz</span>
                    </button>
                    <button wire:click="seleccionarAcompanamiento('fideo')" class="pos-sel-btn">
                        <span class="pos-sel-btn__icon">🍝</span>
                        <span class="pos-sel-btn__label">Fideo</span>
                    </button>
                    <button wire:click="seleccionarAcompanamiento('mixto')" class="pos-sel-btn">
                        <span class="pos-sel-btn__icon">🍱</span>
                        <span class="pos-sel-btn__label">Mixto</span>
                    </button>
                </div>
                <button wire:click="cancelarSelector" class="pos-selector__cancel">Cancelar</button>
            </div>
        </div>
    @endif

    {{-- ══ MODAL INICIO DE CAJA ══ --}}
    @if($mostrar_modal_caja)
        <div class="pos-selector-overlay">
            <div class="pos-selector" wire:click.stop>
                <div class="pos-selector__handle"></div>
                <div class="pos-selector__nombre">
                    <i class="fa-solid fa-cash-register me-2 text-warning"></i>Iniciar Caja
                </div>
                <p class="pos-selector__sub">Primer movimiento del día. Ingresa el monto de cambio inicial.</p>
                <div class="px-3 pb-3 w-100">
                    <input type="number"
                           wire:model="monto_caja"
                           class="form-control form-control-lg text-center"
                           placeholder="0.00"
                           min="0.01"
                           step="0.01"
                           autofocus
                           wire:keydown.enter="confirmarInicioCaja">
                    @error('monto_caja')
                        <div class="text-danger small mt-1 text-center">{{ $message }}</div>
                    @enderror
                </div>
                <div class="pos-selector__opciones" style="grid-template-columns: 1fr;">
                    <button wire:click="confirmarInicioCaja" class="pos-sel-btn" style="background:#198754;color:#fff;border-color:#198754;">
                        <span class="pos-sel-btn__icon">💵</span>
                        <span class="pos-sel-btn__label">Confirmar</span>
                    </button>
                </div>
                <button wire:click="cancelarInicioCaja" class="pos-selector__cancel">Cancelar</button>
            </div>
        </div>
    @endif

    {{-- ══ OVERLAY COBRO ══ --}}
    @php $qrUrl = $qrImagen ? asset('storage/' . $qrImagen) : null; @endphp
    <div x-data="cobroOverlay(@js($qrUrl), @js($waEnabled))"
         x-show="abierto"
         x-cloak
         wire:ignore
         @abrir-cobro.window="abrir($event.detail.total)"
         class="pos-cobro-overlay">

        <div class="pos-cobro-panel" @click.stop>

            {{-- Overlay procesando --}}
            <div x-show="procesando" x-cloak class="pos-cobro-procesando">
                <div class="pos-cobro-procesando__inner">
                    <div class="pos-cobro-procesando__spinner"></div>
                    <span class="pos-cobro-procesando__label">Procesando pago...</span>
                </div>
            </div>

            {{-- Fase: cobrando --}}
            <template x-if="fase === 'cobrando'">
                <div class="pos-cobro-inner">

                    {{-- Header --}}
                    <div class="pos-cobro-header">
                        <span class="pos-cobro-title">
                            <i class="fa-solid fa-cash-register me-2"></i>Cobrar venta
                        </span>
                        <div class="d-flex align-items-center gap-2">
                            <span class="pos-cobro-total-badge">
                                Total: <strong x-text="'Bs. ' + total.toFixed(2)"></strong>
                            </span>
                            <button class="btn btn-sm btn-outline-secondary px-2 py-1" @click="cerrar()" title="Cancelar">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>
                    </div>

                    {{-- Acumulado --}}
                    <div class="pos-cobro-acumulado">
                        <span class="pos-cobro-acumulado__label">Efectivo ingresado:</span>
                        <span class="pos-cobro-acumulado__monto" x-text="'Bs. ' + acumulado.toFixed(2)"></span>
                        <template x-if="acumulado > 0 && acumulado < total">
                            <span class="pos-cobro-acumulado__pendiente">
                                Pendiente: <strong x-text="'Bs. ' + pendiente().toFixed(2)"></strong>
                            </span>
                        </template>
                    </div>

                    {{-- Layout: QR a la izquierda + billetes a la derecha --}}
                    <div class="pos-cobro-layout">

                        {{-- QR grande --}}
                        <button class="pos-cobro-qr-btn" @click="pagarQR()">
                            <div class="pos-cobro-qr-inner">
                                <template x-if="qrUrl">
                                    <img :src="qrUrl" alt="QR Pago" class="pos-cobro-qr-img">
                                </template>
                                <template x-if="!qrUrl">
                                    <div class="pos-cobro-qr-empty">
                                        <i class="fa-solid fa-qrcode fa-4x opacity-30"></i>
                                        <span class="mt-2 small">Sin QR configurado</span>
                                    </div>
                                </template>
                            </div>
                            <div class="pos-cobro-qr-label">
                                <i class="fa-solid fa-mobile-screen me-1"></i>Pago Online
                            </div>
                        </button>

                        {{-- Billetes --}}
                        <div class="pos-cobro-bills">
                            <button class="billete billete--200" @click="agregar(200)">
                                <div class="billete__corner billete__corner--tl">200</div>
                                <div class="billete__corner billete__corner--br">200</div>
                                <div class="billete__watermark">Bs</div>
                                <div class="billete__center">
                                    <span class="billete__val">200</span>
                                    <span class="billete__cur">BOLIVIANOS</span>
                                </div>
                            </button>
                            <button class="billete billete--100" @click="agregar(100)">
                                <div class="billete__corner billete__corner--tl">100</div>
                                <div class="billete__corner billete__corner--br">100</div>
                                <div class="billete__watermark">Bs</div>
                                <div class="billete__center">
                                    <span class="billete__val">100</span>
                                    <span class="billete__cur">BOLIVIANOS</span>
                                </div>
                            </button>
                            <button class="billete billete--50" @click="agregar(50)">
                                <div class="billete__corner billete__corner--tl">50</div>
                                <div class="billete__corner billete__corner--br">50</div>
                                <div class="billete__watermark">Bs</div>
                                <div class="billete__center">
                                    <span class="billete__val">50</span>
                                    <span class="billete__cur">BOLIVIANOS</span>
                                </div>
                            </button>
                            <button class="billete billete--20" @click="agregar(20)">
                                <div class="billete__corner billete__corner--tl">20</div>
                                <div class="billete__corner billete__corner--br">20</div>
                                <div class="billete__watermark">Bs</div>
                                <div class="billete__center">
                                    <span class="billete__val">20</span>
                                    <span class="billete__cur">BOLIVIANOS</span>
                                </div>
                            </button>
                            <button class="billete billete--10" @click="agregar(10)">
                                <div class="billete__corner billete__corner--tl">10</div>
                                <div class="billete__corner billete__corner--br">10</div>
                                <div class="billete__watermark">Bs</div>
                                <div class="billete__center">
                                    <span class="billete__val">10</span>
                                    <span class="billete__cur">BOLIVIANOS</span>
                                </div>
                            </button>
                            <button class="billete billete--5" @click="agregar(5)">
                                <div class="billete__corner billete__corner--tl">5</div>
                                <div class="billete__corner billete__corner--br">5</div>
                                <div class="billete__watermark">Bs</div>
                                <div class="billete__center">
                                    <span class="billete__val">5</span>
                                    <span class="billete__cur">BOLIVIANOS</span>
                                </div>
                            </button>
                            <button class="billete billete--exacto" @click="pagarExacto()">
                                <div class="billete__watermark">✓</div>
                                <div class="billete__center">
                                    <span class="billete__val billete__val--sm" x-text="'Bs. ' + total.toFixed(2)"></span>
                                    <span class="billete__cur">PAGO EXACTO</span>
                                </div>
                            </button>
                        </div>
                    </div>

                </div>
            </template>

            {{-- Fase: cambio --}}
            <template x-if="fase === 'cambio'">
                <div class="pos-cobro-inner pos-cobro-inner--cambio">
                    <div class="pos-cobro-check">
                        <i class="fa-solid fa-circle-check pos-cobro-check__icon"></i>
                    </div>
                    <p class="pos-cobro-cambio__title">¡Venta cobrada!</p>

                    {{-- Pago mixto: desglose --}}
                    <template x-if="onlinePagado > 0 && acumulado > 0">
                        <div class="pos-cobro-desglose">
                            <div class="pos-cobro-desglose__fila">
                                <span><i class="fa-solid fa-money-bill me-1"></i>Efectivo</span>
                                <strong x-text="'Bs. ' + acumulado.toFixed(2)"></strong>
                            </div>
                            <div class="pos-cobro-desglose__fila">
                                <span><i class="fa-solid fa-mobile-screen me-1"></i>Online</span>
                                <strong x-text="'Bs. ' + onlinePagado.toFixed(2)"></strong>
                            </div>
                        </div>
                    </template>

                    <template x-if="cambio > 0">
                        <div class="pos-cobro-cambio__card">
                            <p class="pos-cobro-cambio__sub">Cambio a entregar</p>
                            <div class="pos-cobro-cambio__monto" x-text="'Bs. ' + cambio.toFixed(2)"></div>
                        </div>
                    </template>

                    <button class="pos-cobro-cambio__ok" @click="cerrar()">
                        <i class="fa-solid fa-check me-2"></i>OK
                    </button>
                </div>
            </template>

            {{-- Fase: comprobante (cámara para foto del comprobante QR) --}}
            <template x-if="fase === 'comprobante'">
                <div class="pos-cobro-inner pos-cobro-inner--comprobante">

                    <div class="pos-cobro-header">
                        <span class="pos-cobro-title">
                            <i class="fa-brands fa-whatsapp me-2" style="color:#25d366"></i>Comprobante de pago
                        </span>
                        <button class="btn btn-sm btn-outline-secondary px-2 py-1" @click="saltarFoto()" title="Omitir">
                            Omitir <i class="fa-solid fa-forward ms-1"></i>
                        </button>
                    </div>

                    <p class="text-muted small text-center mb-2">
                        Toma foto del comprobante del cliente para enviarlo al administrador por WhatsApp.
                    </p>

                    {{-- Área de cámara / preview --}}
                    <div class="pos-cobro-camara">
                        <div x-show="!fotoBase64" style="width:100%">
                            <div x-show="cameraError" class="pos-cobro-camara__error">
                                <i class="fa-solid fa-camera-slash fa-2x d-block mb-2"></i>
                                <span x-text="cameraError"></span>
                            </div>
                            <video x-show="!cameraError"
                                   data-wa-camera
                                   autoplay
                                   playsinline
                                   muted
                                   :style="currentFacing === 'user' ? 'transform:scaleX(-1)' : ''"
                                   class="pos-cobro-camara__video"></video>
                        </div>
                        <div x-show="fotoBase64" style="width:100%">
                            <img :src="fotoBase64" class="pos-cobro-camara__img" alt="Comprobante">
                        </div>
                    </div>

                    {{-- Botones de acción --}}
                    <div class="pos-cobro-camara__actions">

                        {{-- Sin foto: Capturar + Cambiar cámara --}}
                        <div x-show="!fotoBase64" class="pos-cobro-camara__btn-row">
                            <button class="pos-cobro-camara__btn pos-cobro-camara__btn--secondary"
                                    @click="switchCamera()"
                                    :disabled="!!cameraError"
                                    title="Cambiar cámara">
                                <i class="fa-solid fa-camera-rotate"></i>
                                <span>Voltear</span>
                            </button>
                            <button class="pos-cobro-camara__btn pos-cobro-camara__btn--primary"
                                    @click="capturar()"
                                    :disabled="!!cameraError">
                                <i class="fa-solid fa-camera"></i>
                                <span>Capturar</span>
                            </button>
                        </div>

                        {{-- Con foto: Retomar + Enviar --}}
                        <div x-show="fotoBase64" class="pos-cobro-camara__btn-row">
                            <button class="pos-cobro-camara__btn pos-cobro-camara__btn--secondary"
                                    @click="retomar()">
                                <i class="fa-solid fa-rotate-left"></i>
                                <span>Retomar</span>
                            </button>
                            <button class="pos-cobro-camara__btn pos-cobro-camara__btn--success"
                                    @click="enviarFoto()"
                                    :disabled="enviando">
                                <span x-show="!enviando">
                                    <i class="fa-brands fa-whatsapp"></i>
                                    <span>Enviar</span>
                                </span>
                                <span x-show="enviando">
                                    <i class="fa-solid fa-spinner fa-spin"></i>
                                    <span>Enviando...</span>
                                </span>
                            </button>
                        </div>

                    </div>

                </div>
            </template>
                        </div>
                    </div>

                </div>
            </template>

        </div>
    </div>

    <style>
        /* ── Overlay cobro ────────────────────────────────────────── */
        .pos-cobro-overlay {
            position: fixed;
            inset: 0;
            z-index: 2000;
            background: rgba(0,0,0,.75);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: .75rem;
        }
        .pos-cobro-panel {
            background: #1a1a2e;
            border-radius: 1.25rem;
            width: 100%;
            max-width: 780px;
            max-height: 96vh;
            overflow-y: auto;
            box-shadow: 0 24px 80px rgba(0,0,0,.6);
            position: relative;
        }

        /* ── Toggles impresión ── */
        .pos-print-toggle {
            width: 30px;
            height: 30px;
            border-radius: 6px;
            border: 1.5px solid #dee2e6;
            background: #f1f3f5;
            color: #adb5bd;
            font-size: .82rem;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background .15s, color .15s, border-color .15s;
            padding: 0;
        }
        .pos-print-toggle--on {
            background: #e7f5ee;
            border-color: #20c997;
            color: #0ca678;
        }
        .pos-cobro-inner {
            display: flex;
            flex-direction: column;
            padding: 1rem 1.25rem 1.25rem;
        }
        .pos-cobro-inner--cambio {
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 2rem 1.5rem 2.5rem;
            gap: .5rem;
        }
        /* Ícono check grande */
        .pos-cobro-check__icon {
            font-size: 4.5rem;
            color: #22c55e;
            filter: drop-shadow(0 0 18px rgba(34,197,94,.5));
        }
        /* Título venta cobrada */
        .pos-cobro-cambio__title {
            font-size: 1.75rem;
            font-weight: 800;
            color: #f1f5f9;
            margin: .25rem 0 0;
            letter-spacing: -.02em;
        }
        /* Tarjeta de cambio */
        .pos-cobro-cambio__card {
            background: #fef08a22;
            border: 2px solid #fde047;
            border-radius: 1rem;
            padding: 1rem 2rem;
            margin-top: .75rem;
            width: 100%;
        }
        .pos-cobro-cambio__sub {
            font-size: .85rem;
            font-weight: 600;
            color: #fde047;
            text-transform: uppercase;
            letter-spacing: .1em;
            margin: 0 0 .25rem;
        }
        .pos-cobro-cambio__monto {
            font-size: 3rem;
            font-weight: 900;
            color: #fef08a;
            line-height: 1;
            letter-spacing: -.02em;
            text-shadow: 0 2px 12px rgba(254,240,138,.4);
        }
        /* Botón OK */
        .pos-cobro-cambio__ok {
            margin-top: 1.25rem;
            background: #16a34a;
            color: #fff;
            border: none;
            border-radius: .75rem;
            font-size: 1.15rem;
            font-weight: 700;
            padding: .75rem 3rem;
            cursor: pointer;
            box-shadow: 0 4px 16px rgba(22,163,74,.4);
            transition: transform .1s, filter .1s;
        }
        .pos-cobro-cambio__ok:active { transform: scale(.95); filter: brightness(1.1); }
        .pos-cobro-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: .6rem;
        }
        .pos-cobro-title {
            font-size: 1rem;
            font-weight: 700;
            color: #e2e8f0;
        }
        .pos-cobro-total-badge {
            background: #166534;
            color: #bbf7d0;
            border: 1px solid #22c55e44;
            border-radius: .5rem;
            padding: .2rem .65rem;
            font-size: .88rem;
        }
        .pos-cobro-acumulado {
            display: flex;
            align-items: center;
            gap: .5rem;
            background: #16213e;
            border-radius: .5rem;
            padding: .45rem 1rem;
            margin-bottom: .85rem;
            border: 1px solid #334155;
        }
        .pos-cobro-acumulado__label { font-size: .78rem; color: #94a3b8; }
        .pos-cobro-acumulado__monto { font-size: 1.25rem; font-weight: 800; color: #60a5fa; margin-left: auto; }
        .pos-cobro-acumulado__pendiente {
            font-size: .75rem;
            color: #fbbf24;
            white-space: nowrap;
            margin-left: .5rem;
        }

        /* ── Layout QR + billetes ── */
        .pos-cobro-layout {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: .85rem;
            align-items: start;
        }
        @media (max-width: 520px) {
            .pos-cobro-layout { grid-template-columns: 1fr; }
        }

        /* ── QR grande ── */
        .pos-cobro-qr-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            background: #fff;
            border: 3px solid #a78bfa;
            border-radius: 1rem;
            padding: .75rem .75rem .5rem;
            cursor: pointer;
            transition: border-color .15s, box-shadow .15s, transform .1s;
            width: 100%;
        }
        .pos-cobro-qr-btn:hover {
            border-color: #7c3aed;
            box-shadow: 0 0 0 4px rgba(124,58,237,.25);
        }
        .pos-cobro-qr-btn:active { transform: scale(.97); }
        .pos-cobro-qr-inner {
            width: 100%;
            aspect-ratio: 1 / 1;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .pos-cobro-qr-img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            border-radius: .5rem;
        }
        .pos-cobro-qr-empty {
            display: flex;
            flex-direction: column;
            align-items: center;
            color: #6d28d9;
            padding: 2rem 0;
        }
        .pos-cobro-qr-label {
            margin-top: .5rem;
            font-size: .82rem;
            font-weight: 700;
            color: #7c3aed;
            letter-spacing: .03em;
        }

        /* ── Billetes grid ── */
        .pos-cobro-bills {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: .55rem;
        }

        /* ── Base billete ── */
        .billete {
            position: relative;
            overflow: hidden;
            border-radius: .65rem;
            border: none;
            cursor: pointer;
            padding: .6rem .5rem;
            min-height: 88px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: transform .1s, box-shadow .1s, filter .1s;
            box-shadow: 0 4px 12px rgba(0,0,0,.4), inset 0 1px 0 rgba(255,255,255,.15);
        }
        .billete:active { transform: scale(.93); filter: brightness(1.15); }
        .billete:hover  { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(0,0,0,.5), inset 0 1px 0 rgba(255,255,255,.2); }

        /* Borde interior decorativo */
        .billete::before {
            content: '';
            position: absolute;
            inset: 5px;
            border: 1px dashed rgba(255,255,255,.25);
            border-radius: .35rem;
            pointer-events: none;
        }

        /* Marca de agua */
        .billete__watermark {
            position: absolute;
            font-size: 3.5rem;
            font-weight: 900;
            color: rgba(255,255,255,.08);
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            pointer-events: none;
            user-select: none;
            letter-spacing: -.05em;
        }

        /* Esquinas */
        .billete__corner {
            position: absolute;
            font-size: .6rem;
            font-weight: 700;
            color: rgba(255,255,255,.55);
            line-height: 1;
        }
        .billete__corner--tl { top: 7px; left: 8px; }
        .billete__corner--br { bottom: 7px; right: 8px; }

        /* Centro */
        .billete__center {
            position: relative;
            z-index: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1px;
        }
        .billete__val {
            font-size: 1.9rem;
            font-weight: 900;
            color: #fff;
            line-height: 1;
            text-shadow: 0 2px 6px rgba(0,0,0,.4);
            letter-spacing: -.02em;
        }
        .billete__val--sm { font-size: 1.45rem; font-weight: 900; }
        .billete__cur {
            font-size: .5rem;
            font-weight: 700;
            color: rgba(255,255,255,.7);
            letter-spacing: .12em;
            text-transform: uppercase;
        }

        /* Colores por denominación */
        .billete--200 { background: linear-gradient(135deg, #7f1d1d 0%, #b91c1c 50%, #991b1b 100%); }
        .billete--100 { background: linear-gradient(135deg, #78350f 0%, #b45309 50%, #92400e 100%); }
        .billete--50  { background: linear-gradient(135deg, #1e3a8a 0%, #1d4ed8 50%, #1e40af 100%); }
        .billete--20  { background: linear-gradient(135deg, #14532d 0%, #15803d 50%, #166534 100%); }
        .billete--10  { background: linear-gradient(135deg, #713f12 0%, #a16207 50%, #854d0e 100%); }
        .billete--5   { background: linear-gradient(135deg, #134e4a 0%, #0f766e 50%, #115e59 100%); }
        .billete--exacto {
            background: linear-gradient(135deg, #312e81 0%, #4f46e5 50%, #3730a3 100%);
            grid-column: span 2;
            min-height: 92px;
        }
        .billete--exacto .billete__watermark { font-size: 4rem; color: rgba(255,255,255,.06); }

        /* Fase comprobante */
        .pos-cobro-inner--comprobante { padding: 1rem 1.25rem 1.25rem; }
        .pos-cobro-camara {
            width: 100%;
            border-radius: .75rem;
            overflow: hidden;
            background: #000;
            margin: .75rem 0;
            min-height: 200px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .pos-cobro-camara__video {
            width: 100%;
            max-height: 55vh;
            object-fit: cover;
            display: block;
        }
        .pos-cobro-camara__img {
            width: 100%;
            max-height: 55vh;
            object-fit: contain;
            display: block;
        }
        .pos-cobro-camara__error {
            color: #f87171;
            text-align: center;
            padding: 2rem;
        }
        .pos-cobro-camara__actions { margin-top: .75rem; }
        .pos-cobro-camara__btn-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: .6rem;
        }
        .pos-cobro-camara__btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: .35rem;
            padding: .75rem .5rem;
            border-radius: .65rem;
            font-size: .82rem;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: filter .15s;
            line-height: 1;
        }
        .pos-cobro-camara__btn i { font-size: 1.3rem; }
        .pos-cobro-camara__btn:disabled { opacity: .45; cursor: not-allowed; }
        .pos-cobro-camara__btn:not(:disabled):hover { filter: brightness(.9); }
        .pos-cobro-camara__btn--primary  { background: #0d6efd; color: #fff; }
        .pos-cobro-camara__btn--secondary { background: #e9ecef; color: #343a40; }
        .pos-cobro-camara__btn--success   { background: #25d366; color: #fff; }
            margin: .5rem 0 .75rem;
            width: 100%;
            max-width: 240px;
        }
        .pos-cobro-desglose__fila {
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: #e2e8f0;
            font-size: .88rem;
            padding: .2rem 0;
        }
        .pos-cobro-desglose__fila + .pos-cobro-desglose__fila {
            border-top: 1px solid #334155;
        }

        /* ── Overlay procesando ── */
        .pos-cobro-procesando {
            position: absolute;
            inset: 0;
            border-radius: 1.25rem;
            background: rgba(15, 15, 30, .88);
            backdrop-filter: blur(4px);
            z-index: 50;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .pos-cobro-procesando__inner {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1rem;
        }
        .pos-cobro-procesando__spinner {
            width: 52px;
            height: 52px;
            border: 4px solid rgba(99,102,241,.25);
            border-top-color: #6366f1;
            border-radius: 50%;
            animation: spin-overlay .7s linear infinite;
        }
        @keyframes spin-overlay { to { transform: rotate(360deg); } }
        .pos-cobro-procesando__label {
            color: #c7d2fe;
            font-size: 1.05rem;
            font-weight: 600;
            letter-spacing: .03em;
        }
    </style>

</div>

@script
<script>
    // Garantiza que playSound esté disponible sin importar el orden de carga
    if (typeof window.playSound !== 'function') {
        const _sc = {};
        window.playSound = function (name) {
            try {
                if (!_sc[name]) _sc[name] = new Audio(`/storage/sounds/${name}.mp3`);
                const a = _sc[name];
                a.currentTime = 0;
                a.play().catch(() => {});
            } catch (e) {}
        };
    }

    // Listener del evento Livewire registrado aquí para garantizar el orden de carga
    $wire.on('play-sound', ([name]) => {
        window.playSound(name);
    });

    window.cobroOverlay = function (qrUrl, waEnabled) {
        return {
            qrUrl,
            waEnabled,
            abierto: false,
            fase: 'cobrando',
            total: 0,
            acumulado: 0,
            cambio: 0,
            onlinePagado: 0,
            ventaCompletada: false,
            // Cámara / comprobante
            cameraStream: null,
            fotoBase64: null,
            cameraError: null,
            enviando: false,
            procesando: false,

            abrir(total) {
                this.total        = parseFloat(total) || 0;
                this.acumulado    = 0;
                this.cambio       = 0;
                this.onlinePagado = 0;
                this.fotoBase64   = null;
                this.cameraError  = null;
                this.enviando         = false;
                this.procesando       = false;
                this.ventaCompletada  = false;
                this.currentFacing    = 'user';
                this.fase             = 'cobrando';
                this.abierto          = true;
            },

            cerrar() {
                this.stopCamera();
                this.fotoBase64 = null;
                if (this.ventaCompletada) {
                    window.playSound('pagar');
                    this.ventaCompletada = false;
                }
                this.abierto = false;
            },

            pendiente() {
                return Math.max(0, Math.round((this.total - this.acumulado) * 100) / 100);
            },

            async agregar(monto) {
                this.acumulado = Math.round((this.acumulado + monto) * 100) / 100;
                if (this.acumulado >= this.total) {
                    this.cambio = Math.round((this.acumulado - this.total) * 100) / 100;
                    this.onlinePagado = 0;
                    this.procesando = true;
                    try { await $wire.procesarVenta(this.total, 0); } catch(e) {}
                    this.procesando      = false;
                    this.ventaCompletada = true;
                    if (this.cambio > 0) {
                        this.fase = 'cambio';
                    } else {
                        this.cerrar();
                    }
                }
            },

            async pagarExacto() {
                const resto = this.pendiente();
                this.cambio       = 0;
                this.onlinePagado = 0;
                this.procesando   = true;
                try { await $wire.procesarVenta(this.acumulado + resto, 0); } catch(e) {}
                this.procesando      = false;
                this.ventaCompletada = true;
                this.cerrar();
            },

            async pagarQR() {
                const resto = this.pendiente();
                this.onlinePagado = resto;
                this.cambio       = 0;
                this.procesando   = true;
                try { await $wire.procesarVenta(this.acumulado, resto); } catch(e) {}
                this.procesando      = false;
                this.ventaCompletada = true;

                if (this.waEnabled) {
                    this.fase = 'comprobante';
                    this.$nextTick(() => this.openCamera());
                } else {
                    this.cerrar();
                }
            },

            // ─── Cámara ───────────────────────────────────────────────────
            // Cámara activa: 'user' (frontal) por defecto, 'environment' (trasera)
            currentFacing: 'user',

            async openCamera() {
                this.cameraError = null;
                const attach = (stream) => {
                    this.cameraStream = stream;
                    const video = document.querySelector('[data-wa-camera]');
                    if (video) video.srcObject = stream;
                };
                // 1) Cámara preferida (frontal por defecto)
                try {
                    attach(await navigator.mediaDevices.getUserMedia({
                        video: { facingMode: { exact: this.currentFacing } },
                        audio: false,
                    }));
                    return;
                } catch (_) { /* seguir con fallback */ }
                // 2) Cualquier cámara disponible
                try {
                    attach(await navigator.mediaDevices.getUserMedia({
                        video: true,
                        audio: false,
                    }));
                } catch (e) {
                    this.cameraError = 'No se pudo acceder a la cámara: ' + (e.message || e.name);
                }
            },

            async switchCamera() {
                this.stopCamera();
                this.currentFacing = this.currentFacing === 'user' ? 'environment' : 'user';
                await this.openCamera();
            },

            stopCamera() {
                if (this.cameraStream) {
                    this.cameraStream.getTracks().forEach(t => t.stop());
                    this.cameraStream = null;
                }
            },

            capturar() {
                const video = document.querySelector('[data-wa-camera]');
                if (!video) return;

                const vw = video.videoWidth  || 640;
                const vh = video.videoHeight || 480;
                // Rotar a vertical si la pantalla es portrait pero el video viene landscape (móvil)
                const needsRotation = (window.innerHeight > window.innerWidth) && (vw > vh);

                const canvas = document.createElement('canvas');
                const ctx    = canvas.getContext('2d');

                if (needsRotation) {
                    // Detectar sentido de rotación según orientación física del dispositivo
                    const angle = screen.orientation ? screen.orientation.angle : (window.orientation ?? 90);
                    const rotateCW = (angle === 270 || angle === -90);

                    canvas.width  = vh; // portrait: ancho = alto original
                    canvas.height = vw; // portrait: alto  = ancho original
                    if (rotateCW) {
                        ctx.translate(vh, 0);
                        ctx.rotate(Math.PI / 2);
                    } else {
                        ctx.translate(0, vw);
                        ctx.rotate(-Math.PI / 2);
                    }
                    ctx.drawImage(video, 0, 0, vw, vh);
                } else {
                    canvas.width  = vw;
                    canvas.height = vh;
                    ctx.drawImage(video, 0, 0);
                }

                this.fotoBase64 = canvas.toDataURL('image/jpeg', 0.7);
                this.stopCamera();
            },

            retomar() {
                this.fotoBase64  = null;
                this.cameraError = null;
                this.$nextTick(() => this.openCamera());
            },

            async enviarFoto() {
                this.enviando = true;
                try {
                    await $wire.enviarComprobanteQR(this.fotoBase64);
                } catch (e) { /* best-effort */ }
                this.enviando = false;
                this.cerrar();
            },

            saltarFoto() {
                this.cerrar();
            },
        };
    };
    // ── Persistir orden de productos (patrón paginate-bar) ──────────────────
    (function () {
        const LS_KEY  = 'pos_orden_productos';
        const COOKIE  = 'pos_orden_productos';
        const saved   = localStorage.getItem(LS_KEY);
        if (saved) {
            // Cookie para PHP (intento; puede no descifrarse si Laravel la encripta)
            document.cookie = `${COOKIE}=${saved};path=/;max-age=31536000;SameSite=Lax`;
            // Corrección segura vía Livewire igual que lo hace paginate-bar
            if (saved !== @js($orden_productos)) {
                $wire.set('orden_productos', saved);
            }
        }
    })();

    document.addEventListener('click', (e) => {
        const btn = e.target.closest('[wire\\:click^="setOrdenProductos"]');
        if (btn) {
            const match = btn.getAttribute('wire:click').match(/setOrdenProductos\('(.*?)'\)/);
            if (match) {
                localStorage.setItem('pos_orden_productos', match[1]);
                document.cookie = `pos_orden_productos=${match[1]};path=/;max-age=31536000;SameSite=Lax`;
            }
        }
    }, true);

    // Envía el UniversalJob al agente local vía fetch() (igual que FADI-V2)
    async function sendToAgent(payload, ventaId) {
        const agentUrl = 'http://localhost:9876/api/print/universal';
        try {
            const controller = new AbortController();
            const tid = setTimeout(() => controller.abort(), 5000);
            const res = await fetch(agentUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload),
                signal: controller.signal,
            });
            clearTimeout(tid);
            if (!res.ok) throw new Error('HTTP ' + res.status);
        } catch (err) {
            // Fallback HTML si el agente no responde
            if (ventaId) window.open(`/ticket/cliente/${ventaId}`, '_blank');
        }
    }

    $wire.on('print-agent', (data) => {
        const d = data[0] || data;
        if (d.payload) sendToAgent(d.payload, d.ventaId ?? null);
    });
</script>
@endscript
