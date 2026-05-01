<?php

namespace App\Livewire;

use App\Helpers\TenantHelper;
use App\Models\Producto;
use App\Models\Venta;
use App\Models\VentaItem;
use App\Models\Movimiento;
use App\Models\Turno;
use App\Traits\WithSwal;
use App\Traits\WithPermisos;
use Carbon\Carbon;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class Pos extends Component
{
    use WithSwal, WithPermisos;

    public $venta_id = null;   // ID de la venta Pendiente activa
    public $carrito = [];      // Espejo en memoria del carrito para renders rÃ¡pidos
    public $total = 0;
    public $tipo_filtro = 'Platos';
    public string $orden_productos = 'popularidad';
    public $mostrar_carrito = false;
    public $producto_pendiente_id = null;
    public $mostrar_selector = false;
    public bool $procesando = false;
    public bool $hay_fideo = true;
    public int  $ultimaVentaNumero = 0;
    public bool $auto_ticket  = true;
    public bool $auto_comanda = true;

    // ─── Por Cobrar ───────────────────────────────────────────────────────────
    public bool $mostrar_por_cobrar_overlay = false;
    public bool $es_venta_por_cobrar = false;

    // ─── Inicio de caja ───────────────────────────────────────────────────────
    public bool $mostrar_modal_caja = false;
    public string $monto_caja = '';
    public ?int $producto_pendiente_caja_id = null;
    public function mount()
    {
        $this->verificarAccesoPOS();
        $this->orden_productos = request()->cookie('pos_orden_productos', 'popularidad');
        $this->hay_fideo       = session('pos_hay_fideo', true);
        $tenant = \App\Helpers\TenantHelper::current();
        $this->auto_ticket  = $tenant?->printer_auto_ticket  ?? config('printer.auto_ticket',  true);
        $this->auto_comanda = $tenant?->printer_auto_comanda ?? config('printer.auto_comanda', true);
        $this->iniciarVentaPendiente();
    }

    public function toggleHayFideo(): void
    {
        $this->hay_fideo = !$this->hay_fideo;
        session(['pos_hay_fideo' => $this->hay_fideo]);
        $this->hay_fideo
            ? $this->swalSuccess('Fideo activado', 'Se agregará opción de fideo a los platos.')
            : $this->swalInfo('Sin fideo', 'La opción de fideo está desactivada.');
    }

    public function toggleAutoComanda(): void
    {
        $this->auto_comanda = !$this->auto_comanda;
        $tenant = \App\Helpers\TenantHelper::current();
        if ($tenant) {
            $tenant->update(['printer_auto_comanda' => $this->auto_comanda]);
        }
        $this->auto_comanda
            ? $this->swalSuccess('Comanda activada', 'Se imprimirá comanda automáticamente en cada venta.')
            : $this->swalWarning('Comanda desactivada', 'No se imprimirá comanda automáticamente.');
    }

    public function toggleAutoTicket(): void
    {
        $this->auto_ticket = !$this->auto_ticket;
        $tenant = \App\Helpers\TenantHelper::current();
        if ($tenant) {
            $tenant->update(['printer_auto_ticket' => $this->auto_ticket]);
        }
        $this->auto_ticket
            ? $this->swalSuccess('Ticket activado', 'Se imprimirá ticket automáticamente en cada venta.')
            : $this->swalWarning('Ticket desactivado', 'No se imprimirá ticket automáticamente.');
    }

    // â”€â”€â”€ Crea (o recupera) la venta Pendiente del usuario actual â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    private function iniciarVentaPendiente(): void
    {
        // Eliminar ventas pendientes que pertenecen a un día comercial anterior
        $tenant = TenantHelper::current();
        $diaHoy = $tenant ? $tenant->businessDayFor(Carbon::now()) : Carbon::today();
        [$inicioDiaComercial] = $tenant
            ? $tenant->businessDayRange($diaHoy)
            : [Carbon::today()->startOfDay(), Carbon::today()->endOfDay()];

        Venta::where('user_id', Auth::id())
            ->where('estado', 'Pendiente')
            ->where('created_at', '<', $inicioDiaComercial)
            ->delete();

        $turnoActivo = $this->getTurnoActivo();

        // Buscar venta Pendiente existente del usuario en este turno
        $query = Venta::where('user_id', Auth::id())->where('estado', 'Pendiente');
        if ($turnoActivo) {
            $query->where('turno_id', $turnoActivo->id);
        }
        $venta = $query->with('ventaItems.producto')->first();

        if (!$venta) {
            $numeroVenta = $this->getNumeroVenta($turnoActivo);
            $venta = Venta::create([
                'user_id'      => Auth::id(),
                'turno_id'     => $turnoActivo?->id,
                'numero_venta' => $numeroVenta,
                'fecha_hora'   => now(),
                'total'        => 0,
                'estado'       => 'Pendiente',
            ]);
        } elseif (is_null($venta->numero_venta)) {
            $venta->update(['numero_venta' => $this->getNumeroVenta($turnoActivo)]);
        }

        $this->venta_id = $venta->id;
        $this->es_venta_por_cobrar = false;
        $this->sincronizarCarritoDesdeDB($venta);
    }

    // â”€â”€â”€ Sincroniza el array $carrito desde los VentaItems de la BD â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    private function sincronizarCarritoDesdeDB(Venta $venta): void
    {
        $this->carrito = [];

        foreach ($venta->ventaItems as $item) {
            if (is_array($item->detalle)) {
                // Platos: impresos → pp_{id} (read-only), sin imprimir → p_{productoId} (editable)
                $key = $item->comanda_impresa ? "pp_{$item->id}" : "p_{$item->producto_id}";
                $acomp = [
                    'arroz' => (int) ($item->detalle['arroz'] ?? 0),
                    'fideo' => (int) ($item->detalle['fideo'] ?? 0),
                    'mixto' => (int) ($item->detalle['mixto'] ?? 0),
                ];
                $this->carrito[$key] = [
                    'tipo'             => 'plato',
                    'producto_id'      => $item->producto_id,
                    'nombre'           => $item->producto->nombre ?? 'Producto',
                    'imagen'           => $item->producto->imagen ?? null,
                    'categoria'        => $item->producto->tipo ?? 'Platos',
                    'precio'           => (float) $item->precio,
                    'item_id'          => $item->id,
                    'acomp'            => $acomp,
                    'cantidad'         => array_sum($acomp),
                    'subtotal'         => array_sum($acomp) * (float) $item->precio,
                    'comanda_impresa'  => (bool) $item->comanda_impresa,
                ];
            } else {
                // Simple: una entrada por producto
                $key = "s_{$item->producto_id}";
                $this->carrito[$key] = [
                    'tipo'            => 'simple',
                    'producto_id'     => $item->producto_id,
                    'nombre'          => $item->producto->nombre ?? 'Producto',
                    'imagen'          => $item->producto->imagen ?? null,
                    'categoria'       => $item->producto->tipo ?? 'Refrescos',
                    'precio'          => (float) $item->precio,
                    'item_id'         => $item->id,
                    'cantidad'        => $item->cantidad,
                    'subtotal'        => (float) $item->subtotal,
                    'comanda_impresa' => (bool) $item->comanda_impresa,
                ];
            }
        }

        // Recalcular subtotales de platos
        foreach (array_keys($this->carrito) as $key) {
            if ($this->carrito[$key]['tipo'] === 'plato') {
                $this->recalcularSubtotalPlato($key);
            }
        }

        $this->calcularTotal();
    }

    public function render()
    {
        $productos = Producto::where('estado', true)
            ->where('tipo', $this->tipo_filtro)
            ->when($this->orden_productos === 'popularidad',
                fn($q) => $q->orderByDesc('total_vendido')->orderBy('nombre'),
                fn($q) => $q->orderBy('nombre')
            )
            ->get();

        $qrImagen  = null;
        $waEnabled = false;
        $turnoActivo = $this->getTurnoActivo();
        if ($turnoActivo?->encargado_id) {
            // QR: sigue siendo del encargado del turno
            $encargado = \App\Models\User::find($turnoActivo->encargado_id);
            $pivot = $encargado?->tenants()
                ->wherePivot('tenant_id', \App\Helpers\TenantHelper::currentId())
                ->first()?->pivot;
            $qrImagen = $pivot?->qr_imagen;
        }

        // WhatsApp: credenciales del tenant, notifica al propietario
        $tenant = \App\Helpers\TenantHelper::current();
        if ($tenant && !empty($tenant->wa_instance_id) && !empty($tenant->wa_api_token)) {
            $ownerUser = \App\Models\User::find($tenant->propietarioId());
            $waEnabled = !empty($ownerUser?->celular);
        }

        $ventasPorCobrar = Venta::where('estado', 'PorCobrar')
            ->with('ventaItems.producto')
            ->orderBy('fecha_hora')
            ->get();

        $hayItemsNuevos = $this->venta_id
            ? VentaItem::where('venta_id', $this->venta_id)->where('comanda_impresa', false)->exists()
            : false;

        return view('livewire.pos', compact('productos', 'qrImagen', 'waEnabled', 'ventasPorCobrar', 'hayItemsNuevos'));
    }

    public function setOrdenProductos(string $orden): void
    {
        $this->orden_productos = $orden;
        $label = $orden === 'nombre' ? 'A-Z' : 'Más vendidos primero';
        $this->swalInfo('Orden cambiado', "Mostrando productos: {$label}.");
    }

    // â”€â”€â”€ Agregar al carrito â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    public function agregarAlCarrito($productoId)
    {
        // Verificar que haya un turno activo esta semana
        if (!$this->getTurnoActivo()) {
            if ($this->esAdmin()) {
                $this->redirect(route('turnos'), navigate: false);
            } else {
                $this->swalWarning('Sin turno activo', 'No hay un turno abierto para esta semana. Contacta al administrador.');
            }
            return;
        }

        // Verificar si existe movimiento hoy después de las 02:00
        if (!$this->verificarMovimientosHoy()) {
            // Bloquear si está fuera del horario de atención
            $tenant = TenantHelper::current();
            if ($tenant && !$tenant->estaEnHorario()) {
                $horaApertura = substr($tenant->horario_inicio, 0, 5);
                $this->swalWarning(
                    'Fuera de horario',
                    "El día comercial aún no ha iniciado. El horario de apertura es a las {$horaApertura}."
                );
                return;
            }
            $this->producto_pendiente_caja_id = $productoId;
            $this->monto_caja = '';
            $this->mostrar_modal_caja = true;
            return;
        }

        $producto = Producto::find($productoId);

        if ($producto->tipo === 'Platos') {
            if (!$this->hay_fideo) {
                // Sin fideo: asumir arroz directamente
                $this->addToCart($productoId, 'arroz');
                return;
            }
            $this->producto_pendiente_id = $productoId;
            $this->mostrar_selector = true;
            return;
        }

        $this->addToCart($productoId, null);
    }

    // ─── Verifica si ya hay movimientos en el día comercial actual ──────────
    private function verificarMovimientosHoy(): bool
    {
        $turnoActivo = $this->getTurnoActivo();

        if (!$turnoActivo) {
            return true; // Sin turno activo, permitir flujo normal
        }

        $tenant = TenantHelper::current();
        $diaHoy = $tenant ? $tenant->businessDayFor(Carbon::now()) : Carbon::today();
        [$desde] = $tenant ? $tenant->businessDayRange($diaHoy) : [Carbon::today(), Carbon::today()->endOfDay()];

        return Movimiento::where('turno_id', $turnoActivo->id)
            ->where('created_at', '>=', $desde)
            ->exists();
    }

    // ─── Confirmar inicio de caja ─────────────────────────────────────────────
    public function confirmarInicioCaja(): void
    {
        $this->validate([
            'monto_caja' => 'required|numeric|min:0.01',
        ], [
            'monto_caja.required' => 'Ingresa el monto inicial.',
            'monto_caja.numeric'  => 'El monto debe ser un número.',
            'monto_caja.min'      => 'El monto debe ser mayor a 0.',
        ]);

        $turnoActivo = $this->getTurnoActivo();

        if (!$turnoActivo) {
            $this->cancelarInicioCaja();
            return;
        }

        DB::transaction(function () use ($turnoActivo) {
            $saldoActual = (float) (Movimiento::where('turno_id', $turnoActivo->id)
                ->orderBy('id', 'desc')
                ->value('saldo') ?? 0);

            // 1. Egresar todo el saldo anterior dejándolo en 0
            if ($saldoActual > 0) {
                Movimiento::create([
                    'turno_id' => $turnoActivo->id,
                    'user_id'  => Auth::id(),
                    'detalle'  => 'Retiro de saldo al iniciar caja',
                    'ingreso'  => 0,
                    'egreso'   => $saldoActual,
                    'saldo'    => 0,
                ]);
            }

            // 2. Ingresar el monto como cambio inicial de caja
            Movimiento::create([
                'turno_id' => $turnoActivo->id,
                'user_id'  => Auth::id(),
                'detalle'  => 'Inicio de caja',
                'ingreso'  => (float) $this->monto_caja,
                'egreso'   => 0,
                'saldo'    => (float) $this->monto_caja,
            ]);
        });

        $productoPendienteId = $this->producto_pendiente_caja_id;
        $this->cancelarInicioCaja();

        // Continuar con el flujo normal de añadir el producto
        if ($productoPendienteId) {
            $this->agregarAlCarrito($productoPendienteId);
        }
    }

    // ─── Cancelar inicio de caja ──────────────────────────────────────────────
    public function cancelarInicioCaja(): void
    {
        $this->mostrar_modal_caja = false;
        $this->monto_caja = '';
        $this->producto_pendiente_caja_id = null;
        $this->resetValidation('monto_caja');
    }

    public function seleccionarAcompanamiento($acompanamiento)
    {
        $this->addToCart($this->producto_pendiente_id, $acompanamiento);
        $this->mostrar_selector = false;
        $this->producto_pendiente_id = null;
    }

    public function cancelarSelector()
    {
        $this->mostrar_selector = false;
        $this->producto_pendiente_id = null;
    }

    private function addToCart($productoId, $detalle): void
    {
        $producto = Producto::find($productoId);

        if ($detalle !== null) {
            // ── Platos: un único VentaItem con detalle como JSON ───────────────
            $key = "p_{$productoId}";

            if (!isset($this->carrito[$key])) {
                $acomp = ['arroz' => 0, 'fideo' => 0, 'mixto' => 0];
                $acomp[$detalle] = 1;
                $item = VentaItem::create([
                    'venta_id'    => $this->venta_id,
                    'producto_id' => $producto->id,
                    'cantidad'    => 1,
                    'precio'      => $producto->precio,
                    'subtotal'    => $producto->precio,
                    'detalle'     => $acomp,
                ]);
                $this->carrito[$key] = [
                    'tipo'            => 'plato',
                    'producto_id'     => $producto->id,
                    'nombre'          => $producto->nombre,
                    'imagen'          => $producto->imagen ?? null,
                    'categoria'       => $producto->tipo ?? 'Platos',
                    'precio'          => (float) $producto->precio,
                    'item_id'         => $item->id,
                    'acomp'           => $acomp,
                    'cantidad'        => 1,
                    'subtotal'        => (float) $producto->precio,
                    'comanda_impresa' => false,
                ];
            } else {
                $this->carrito[$key]['acomp'][$detalle]++;
                $nuevaCantidad = array_sum($this->carrito[$key]['acomp']);
                $nuevoSubtotal = $nuevaCantidad * $this->carrito[$key]['precio'];
                VentaItem::where('id', $this->carrito[$key]['item_id'])->update([
                    'cantidad' => $nuevaCantidad,
                    'subtotal' => $nuevoSubtotal,
                    'detalle'  => $this->carrito[$key]['acomp'],
                ]);
                $this->carrito[$key]['cantidad'] = $nuevaCantidad;
                $this->carrito[$key]['subtotal'] = $nuevoSubtotal;
            }

            $this->recalcularSubtotalPlato($key);

        } else {
            // ── Simple: una entrada por producto ──────────────────────────────
            $key = "s_{$productoId}";

            if (isset($this->carrito[$key])) {
                $nueva = $this->carrito[$key]['cantidad'] + 1;
                VentaItem::where('id', $this->carrito[$key]['item_id'])->update([
                    'cantidad' => $nueva,
                    'subtotal' => $nueva * $producto->precio,
                ]);
                $this->carrito[$key]['cantidad'] = $nueva;
                $this->carrito[$key]['subtotal'] = $nueva * $producto->precio;
            } else {
                $item = VentaItem::create([
                    'venta_id'    => $this->venta_id,
                    'producto_id' => $producto->id,
                    'cantidad'    => 1,
                    'precio'      => $producto->precio,
                    'subtotal'    => $producto->precio,
                    'detalle'     => null,
                ]);
                $this->carrito[$key] = [
                    'tipo'            => 'simple',
                    'producto_id'     => $producto->id,
                    'nombre'          => $producto->nombre,
                    'imagen'          => $producto->imagen ?? null,
                    'categoria'       => $producto->tipo ?? 'Refrescos',
                    'precio'          => (float) $producto->precio,
                    'item_id'         => $item->id,
                    'cantidad'        => 1,
                    'subtotal'        => (float) $producto->precio,
                    'comanda_impresa' => false,
                ];
            }
        }

        $this->calcularTotal();
        $this->actualizarTotalVenta();
        $this->dispatch('play-sound', 'agregar');
    }

    public function aumentarCantidad($key): void
    {
        if (!isset($this->carrito[$key]) || $this->carrito[$key]['tipo'] !== 'simple') return;

        $nueva = $this->carrito[$key]['cantidad'] + 1;
        VentaItem::where('id', $this->carrito[$key]['item_id'])->update([
            'cantidad' => $nueva,
            'subtotal' => $nueva * $this->carrito[$key]['precio'],
        ]);
        $this->carrito[$key]['cantidad'] = $nueva;
        $this->carrito[$key]['subtotal'] = $nueva * $this->carrito[$key]['precio'];
        $this->calcularTotal();
        $this->actualizarTotalVenta();
    }

    public function disminuirCantidad($key): void
    {
        if (!isset($this->carrito[$key]) || $this->carrito[$key]['tipo'] !== 'simple') return;

        if ($this->carrito[$key]['cantidad'] > 1) {
            $nueva = $this->carrito[$key]['cantidad'] - 1;
            VentaItem::where('id', $this->carrito[$key]['item_id'])->update([
                'cantidad' => $nueva,
                'subtotal' => $nueva * $this->carrito[$key]['precio'],
            ]);
            $this->carrito[$key]['cantidad'] = $nueva;
            $this->carrito[$key]['subtotal'] = $nueva * $this->carrito[$key]['precio'];
        } else {
            VentaItem::destroy($this->carrito[$key]['item_id']);
            unset($this->carrito[$key]);
        }

        $this->calcularTotal();
        $this->actualizarTotalVenta();
    }

    public function actualizarAcompanamiento(string $key, string $detalle, $cantidad): void
    {
        $cantidad = max(0, (int) $cantidad);
        if (!isset($this->carrito[$key])) return;

        $producto = Producto::find($this->carrito[$key]['producto_id']);

        $this->carrito[$key]['acomp'][$detalle] = $cantidad;
        $nuevaCantidad = array_sum($this->carrito[$key]['acomp']);

        if ($nuevaCantidad === 0) {
            VentaItem::destroy($this->carrito[$key]['item_id']);
            unset($this->carrito[$key]);
        } else {
            $nuevoSubtotal = $nuevaCantidad * $this->carrito[$key]['precio'];
            VentaItem::where('id', $this->carrito[$key]['item_id'])->update([
                'cantidad' => $nuevaCantidad,
                'subtotal' => $nuevoSubtotal,
                'detalle'  => $this->carrito[$key]['acomp'],
            ]);
            $this->carrito[$key]['cantidad'] = $nuevaCantidad;
            $this->carrito[$key]['subtotal'] = $nuevoSubtotal;
            // Forzar reactividad en Livewire 3 con arrays anidados
            $entry = $this->carrito[$key];
            $this->carrito[$key] = $entry;
        }

        $this->calcularTotal();
        $this->actualizarTotalVenta();
    }

    public function eliminarDelCarrito($key): void
    {
        if (!isset($this->carrito[$key])) return;

        $item = $this->carrito[$key];
        VentaItem::destroy($item['item_id']);

        unset($this->carrito[$key]);
        $this->calcularTotal();
        $this->actualizarTotalVenta();
        $this->dispatch('play-sound', 'eliminar');
    }

    public function vaciarCarrito(): void
    {
        VentaItem::where('venta_id', $this->venta_id)->delete();
        $this->carrito = [];
        $this->total = 0;
        $this->actualizarTotalVenta();
        $this->showSuccessNotification('Carrito vaciado');
    }

    public function setTipoFiltro($tipo): void
    {
        $this->tipo_filtro = $tipo;
        // En móvil, al cambiar de categoría mostramos el catálogo
        $this->mostrar_carrito = false;
    }

    public function toggleCarrito(): void
    {
        $this->mostrar_carrito = !$this->mostrar_carrito;
    }

    // â”€â”€â”€ Completar venta â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    public function procesarVenta(float $efectivo = 0, float $online = 0): void
    {
        if (empty($this->carrito)) {
            $this->showErrorNotification('El carrito está vacío');
            return;
        }

        if ($this->procesando) return;
        $this->procesando = true;

        try {
            DB::beginTransaction();

            $venta = Venta::findOrFail($this->venta_id);
            $turnoActivo = $this->getTurnoActivo();

            // Marcar como Completo
            $venta->update([
                'estado'    => 'Completo',
                'total'     => $this->total,
                'efectivo'  => $efectivo,
                'online'    => $online,
                'fecha_hora'=> now(),
            ]);

            // Registrar movimiento de ingreso
            if ($turnoActivo) {
                $saldo = Movimiento::where('turno_id', $turnoActivo->id)->orderBy('id', 'desc')->value('saldo') ?? 0;
                Movimiento::create([
                    'turno_id' => $turnoActivo->id,
                    'user_id'  => Auth::id(),
                    'detalle'  => 'Venta #' . $venta->numero_venta,
                    'ingreso'  => $this->total,
                    'egreso'   => 0,
                    'saldo'    => $saldo + $this->total,
                ]);
            }

            DB::commit();

            // Incrementar contador de popularidad de cada producto vendido
            foreach ($this->carrito as $item) {
                Producto::where('id', $item['producto_id'])
                    ->increment('total_vendido', $item['cantidad']);
            }

            // Guardar el ID antes de reiniciar el estado
            $ventaCompletadaId = $venta->id;
            $this->ultimaVentaNumero = $venta->numero_venta ?? 0;

            // Notificación WhatsApp (best-effort, no bloquea el flujo)
            $this->notificarVentaWhatsapp($turnoActivo, $efectivo, $online);

            // Iniciar nueva venta pendiente
            $this->venta_id = null;
            $this->carrito = [];
            $this->total = 0;
            $this->mostrar_carrito = false;
            $this->procesando = false;
            $this->iniciarVentaPendiente();

        } catch (\Exception $e) {
            DB::rollBack();
            $this->procesando = false;
            $this->showErrorNotification('Error al procesar la venta: ' . $e->getMessage());
            return;
        }

        // Impresión automática fuera del try-catch de BD
        // El servidor construye el UniversalJob cifrado y lo despacha al JS del cliente.
        // El JS hace fetch() a localhost:9876 — el agente corre en la PC del cliente.
        try {
            $ventaParaImprimir = \App\Models\Venta::with(['items.producto', 'turno.encargado', 'usuario'])
                ->find($ventaCompletadaId);
            $svc    = app(\App\Services\EscposPrintService::class);
            $tenant = \App\Helpers\TenantHelper::current();

            if ($this->auto_comanda) {
                $built = $svc->buildComandaJob($ventaParaImprimir);
                if ($built['ok']) {
                    $this->dispatch('print-agent',
                        payload: array_merge(['printer' => $built['printer']], $built['job']),
                        ventaId: $ventaCompletadaId
                    );
                    // Marcar items enviados a cocina como ya impresos
                    VentaItem::where('venta_id', $ventaCompletadaId)
                        ->where('comanda_impresa', false)
                        ->update(['comanda_impresa' => true]);
                }
            }

            if ($this->auto_ticket) {
                $built = $svc->buildTicketJob($ventaParaImprimir);
                if ($built['ok']) {
                    $this->dispatch('print-agent',
                        payload: array_merge(['printer' => $built['printer']], $built['job']),
                        ventaId: $ventaCompletadaId
                    );
                }
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Error de impresión post-venta: ' . $e->getMessage());
        }
    }

    // ─── WhatsApp ─────────────────────────────────────────────────────────────

    /**
     * Envía el detalle de la venta al admin vía WhatsApp (best-effort).
     */
    private function notificarVentaWhatsapp(?Turno $turno, float $efectivo, float $online): void
    {
        try {
            [$encargado, $pivot] = $this->getEncargadoWA($turno);
            if (!$encargado || !$pivot) return;

            if (!$pivot->wa_notify_ventas
                || empty($pivot->wa_instance_id)
                || empty($pivot->wa_api_token)
                || empty($encargado->celular)) {
                return;
            }

            $lineas = ["🧾 *Venta #{$this->ultimaVentaNumero} completada*\n"];
            foreach ($this->carrito as $item) {
                $sub = number_format($item['subtotal'] ?? ($item['precio'] * $item['cantidad']), 2);
                $lineas[] = "• {$item['nombre']} x{$item['cantidad']} = Bs. {$sub}";
            }
            $lineas[] = "\n─────────────────";
            $lineas[] = "*Total: Bs. " . number_format($this->total, 2) . "*";
            if ($efectivo > 0) $lineas[] = "💵 Efectivo: Bs. " . number_format($efectivo, 2);
            if ($online  > 0) $lineas[] = "📱 Online: Bs. "   . number_format($online,   2);

            (new \App\Services\GreenApiService())->sendMessage(
                $pivot->wa_instance_id,
                $pivot->wa_api_token,
                $encargado->celular,
                implode("\n", $lineas)
            );
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('WA venta notify: ' . $e->getMessage());
        }
    }

    /**
     * Recibe la foto del comprobante (base64) y la envía al admin del turno vía WA.
     */
    public function enviarComprobanteQR(string $base64): void
    {
        try {
            $turno = $this->getTurnoActivo();
            [$encargado, $pivot] = $this->getEncargadoWA($turno);
            if (!$encargado || !$pivot) return;

            if (empty($pivot->wa_instance_id)
                || empty($pivot->wa_api_token)
                || empty($encargado->celular)) {
                return;
            }

            $caption = '📸 Comprobante de pago QR';
            if ($this->ultimaVentaNumero) {
                $caption .= " — Venta #{$this->ultimaVentaNumero}";
            }

            (new \App\Services\GreenApiService())->sendImageBase64(
                $pivot->wa_instance_id,
                $pivot->wa_api_token,
                $encargado->celular,
                $base64,
                $caption
            );
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('WA comprobante: ' . $e->getMessage());
        }
    }

    /**
     * Descarta la edición de una venta PorCobrar sin cancelarla:
     * la devuelve a estado PorCobrar e inicia una nueva venta Pendiente vacía.
     */
    public function descartarEdicionPorCobrar(): void
    {
        try {
            Venta::where('id', $this->venta_id)
                ->where('estado', 'Pendiente')
                ->update(['estado' => 'PorCobrar']);
        } catch (\Exception $e) {
            $this->showErrorNotification('Error al descartar: ' . $e->getMessage());
            return;
        }

        $this->venta_id        = null;
        $this->carrito         = [];
        $this->total           = 0;
        $this->mostrar_carrito = false;
        $this->es_venta_por_cobrar = false;
        $this->iniciarVentaPendiente();
    }

    // ─── Cancelar venta ───────────────────────────────────────────────────────
    public function cancelarVenta(): void
    {
        try {
            DB::beginTransaction();

            // La venta pendiente nunca se completó: se elimina junto con sus items
            Venta::where('id', $this->venta_id)
                ->where('estado', 'Pendiente')
                ->delete();

            DB::commit();

            $this->showSuccessNotification('Pedido cancelado');
            $this->dispatch('play-sound', 'eliminar');

            $this->venta_id = null;
            $this->carrito = [];
            $this->total = 0;
            $this->mostrar_carrito = false;
            $this->iniciarVentaPendiente();

        } catch (\Exception $e) {
            DB::rollBack();
            $this->showErrorNotification('Error al cancelar el pedido: ' . $e->getMessage());
        }
    }

    // â”€â”€â”€ Helpers â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    private function recalcularSubtotalPlato(string $key): void
    {
        $total = array_sum($this->carrito[$key]['acomp']);
        $this->carrito[$key]['cantidad'] = $total;
        $this->carrito[$key]['subtotal'] = $total * $this->carrito[$key]['precio'];
    }
    private function calcularTotal(): void
    {
        $this->total = array_sum(array_column($this->carrito, 'subtotal'));
    }

    private function actualizarTotalVenta(): void
    {
        if ($this->venta_id) {
            Venta::where('id', $this->venta_id)->update(['total' => $this->total]);
        }
    }

    /**
     * Devuelve [User $encargado, pivot] del admin del turno para envíos WA.
     * Siempre usa encargado_id del turno, nunca Auth::id(),
     * para que operadores también envíen al admin correspondiente.
     *
     * Devuelve [propietario, tenant] para las notificaciones WhatsApp.
     * Las credenciales son del tenant y el número de destino es del propietario.
     */
    private function getEncargadoWA(?Turno $turno): array
    {
        $tenant = \App\Helpers\TenantHelper::current();
        if (!$tenant) return [null, null];

        $ownerUser = \App\Models\User::find($tenant->propietarioId());
        return [$ownerUser, $tenant];
    }

    private function getTurnoActivo(): ?Turno
    {
        $inicio = now()->startOfWeek(\Carbon\Carbon::MONDAY)->toDateString();
        $fin    = now()->startOfWeek(\Carbon\Carbon::MONDAY)->addDays(6)->toDateString();

        $query = Turno::where('fecha_inicio', '<=', $fin)->where('fecha_fin', '>=', $inicio);

        if ($this->esAdmin()) {
            $query->where('encargado_id', Auth::id());
        }

        return $query->first();
    }

    private function getNumeroVenta(?Turno $turno): int
    {
        $tenant = TenantHelper::current();
        $diaHoy = $tenant ? $tenant->businessDayFor(Carbon::now()) : Carbon::today();
        [$rangoInicio, $rangoFin] = $tenant
            ? $tenant->businessDayRange($diaHoy)
            : [Carbon::today()->startOfDay(), Carbon::today()->endOfDay()];

        $query = Venta::whereBetween('fecha_hora', [$rangoInicio, $rangoFin])
            ->whereNotIn('estado', ['Pendiente']);

        if ($turno) {
            $query->where('turno_id', $turno->id);
        }

        $ultimo = $query->orderBy('numero_venta', 'desc')->value('numero_venta');
        return $ultimo ? $ultimo + 1 : 1;
    }

    // ─── Por Cobrar ───────────────────────────────────────────────────────────

    /**
     * Marca la venta pendiente actual como "PorCobrar", imprime la comanda
     * y crea una nueva venta Pendiente vacía para seguir atendiendo.
     */
    public function marcarPorCobrar(): void
    {
        if (empty($this->carrito)) {
            $this->swalWarning('Carrito vacío', 'Agrega productos antes de marcar como Por Cobrar.');
            return;
        }

        try {
            DB::beginTransaction();

            $venta = Venta::findOrFail($this->venta_id);
            $turnoActivo = $this->getTurnoActivo();

            // Asignar número de venta si aún no tiene
            if (!$venta->numero_venta) {
                $venta->numero_venta = $this->getNumeroVenta($turnoActivo);
            }

            $venta->update([
                'estado'     => 'PorCobrar',
                'fecha_hora' => now(),
                'total'      => $this->total,
                'numero_venta' => $venta->numero_venta,
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->showErrorNotification('Error: ' . $e->getMessage());
            return;
        }

        $ventaId = $this->venta_id;

        // Mantener carrito visible y pasar al estado "Por Cobrar" para cobrar
        $this->es_venta_por_cobrar = true;

        // Imprimir comanda ANTES de marcar los ítems (buildComandaJob filtra !comanda_impresa)
        if ($this->auto_comanda) {
            try {
                $ventaParaImprimir = \App\Models\Venta::with(['items.producto', 'turno.encargado', 'usuario'])
                    ->find($ventaId);
                $svc   = app(\App\Services\EscposPrintService::class);
                $built = $svc->buildComandaJob($ventaParaImprimir);
                if ($built['ok']) {
                    $this->dispatch('print-agent',
                        payload: array_merge(['printer' => $built['printer']], $built['job']),
                        ventaId: $ventaId
                    );
                    $this->swalSuccess('Comanda enviada', 'La venta quedó en estado Por Cobrar.');
                } else {
                    $this->swalSuccess('Por Cobrar', 'La venta quedó en estado Por Cobrar.');
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('Error impresión comanda PorCobrar: ' . $e->getMessage());
                $this->swalSuccess('Por Cobrar', 'La venta quedó en estado Por Cobrar.');
            }
        } else {
            $this->swalSuccess('Por Cobrar', 'La venta quedó en estado Por Cobrar.');
        }

        // Marcar TODOS los ítems nuevos como impresos (para bloquearlos en UI)
        VentaItem::where('venta_id', $ventaId)
            ->where('comanda_impresa', false)
            ->update(['comanda_impresa' => true]);

        // Refrescar carrito en memoria para que el botón Comanda desaparezca
        foreach (array_keys($this->carrito) as $k) {
            $this->carrito[$k]['comanda_impresa'] = true;
        }

        $this->dispatch('comanda-enviada');
    }

    /**
     * Abre el overlay de ventas Por Cobrar.
     */
    public function abrirVentasPorCobrar(): void
    {
        $this->mostrar_por_cobrar_overlay = true;
    }

    /**
     * Cierra el overlay de ventas Por Cobrar.
     */
    public function cerrarVentasPorCobrar(): void
    {
        $this->mostrar_por_cobrar_overlay = false;
    }

    /**
     * Carga una venta PorCobrar como el carrito activo para seguir editándola o cobrarla.
     * El carrito actual (Pendiente vacío) se elimina y la venta seleccionada pasa a Pendiente.
     */
    public function cargarVentaPorCobrar(int $ventaId): void
    {
        if (!empty($this->carrito)) {
            $this->swalWarning(
                'Carrito activo',
                'Primero marca el pedido actual como "Por Cobrar" o cancélalo antes de cargar otro.'
            );
            return;
        }

        try {
            DB::beginTransaction();

            // Eliminar la venta Pendiente vacía actual
            Venta::where('id', $this->venta_id)
                ->where('estado', 'Pendiente')
                ->delete();

            // Activar la venta PorCobrar como Pendiente
            Venta::where('id', $ventaId)
                ->where('estado', 'PorCobrar')
                ->update(['estado' => 'Pendiente']);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->showErrorNotification('Error: ' . $e->getMessage());
            return;
        }

        $this->venta_id = $ventaId;
        $this->mostrar_por_cobrar_overlay = false;
        $this->es_venta_por_cobrar = true;

        $venta = Venta::with('ventaItems.producto')->find($ventaId);
        $this->sincronizarCarritoDesdeDB($venta);

        $this->swalInfo('Pedido cargado', 'Puedes añadir más productos o cobrar directamente.');
    }

    /**
     * Carga la venta PorCobrar como activa y abre el panel de cobro directamente.
     */
    public function cobrarVentaPorCobrar(int $ventaId): void
    {
        if (!empty($this->carrito)) {
            $this->swalWarning(
                'Carrito activo',
                'Primero marca el pedido actual como "Por Cobrar" o cancélalo antes de cobrar otro.'
            );
            return;
        }

        try {
            DB::beginTransaction();

            Venta::where('id', $this->venta_id)
                ->where('estado', 'Pendiente')
                ->delete();

            Venta::where('id', $ventaId)
                ->where('estado', 'PorCobrar')
                ->update(['estado' => 'Pendiente']);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->showErrorNotification('Error: ' . $e->getMessage());
            return;
        }

        $this->venta_id = $ventaId;
        $this->mostrar_por_cobrar_overlay = false;
        $this->es_venta_por_cobrar = true;

        $venta = Venta::with('ventaItems.producto')->find($ventaId);
        $this->sincronizarCarritoDesdeDB($venta);

        // Abrir panel de cobro con el total actualizado
        $this->dispatch('abrir-cobro', total: $this->total);
    }

    /**
     * Cancela (elimina) una venta PorCobrar desde el overlay.
     */
    public function cancelarVentaPorCobrar(int $ventaId): void
    {
        Venta::where('id', $ventaId)
            ->where('estado', 'PorCobrar')
            ->delete();

        $this->swalSuccess('Pedido eliminado', 'La venta Por Cobrar fue cancelada.');
    }
}
