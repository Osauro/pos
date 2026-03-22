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
                        <i class="fa-solid {{ $ico }} me-1"></i>{{ $cat }}
                    </button>
                @endforeach
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
                        <div class="col-6 col-md-4 col-lg-3 col-xl-2">
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
                    <button wire:click="procesarVenta"
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
        <button wire:click="procesarVenta"
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

</div>

@script
<script>
    // 300px = exactamente 80mm a 96dpi
    const WIN_OPTS = 'toolbar=0,scrollbars=0,location=0,statusbar=0,menubar=0,resizable=0,width=300,height=700';

    // Dispara una URL de protocolo custom (print://) via <a>.click()
    // Chrome solo permite window.location.href una vez por gesto;
    // el click en un <a> no tiene esa restricción.
    function launchProtocol(url, delay = 0) {
        const a = document.createElement('a');
        a.href = url;
        a.style.display = 'none';
        document.body.appendChild(a);
        setTimeout(() => { a.click(); document.body.removeChild(a); }, delay);
    }

    $wire.on('imprimir-venta', (data) => {
        const d        = data[0] || data;
        const ventaId  = d.ventaId;
        const printUrl = d.printUrl ?? null;
        if (!ventaId) return;

        // Solo Android usa fallback HTML/PDF.
        // Windows, iOS, iPad, etc. usan siempre print:// (ESC/POS directo).
        const isAndroid = /Android/i.test(navigator.userAgent);

        if (!isAndroid && printUrl) {
            // ── ESC/POS directo: ticket + comanda en un solo print:// ─────────
            launchProtocol(printUrl);
        } else if (isAndroid) {
            // ── Android: fallback HTML con autoprint ──────────────────────────
            window.open(`/ticket/cliente/${ventaId}?nocomanda=1`, '_blank');
            setTimeout(() => {
                window.open(`/ticket/comanda/${ventaId}`, '_blank');
            }, 10000);
        } else {
            // ── Sin printUrl (escpos desactivado): ventana impresora fallback ──
            window.open(`/ticket/cliente/${ventaId}`, '_blank', WIN_OPTS);
        }
    });
</script>
@endscript
