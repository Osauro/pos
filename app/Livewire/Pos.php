<?php

namespace App\Livewire;

use App\Models\Producto;
use App\Models\Venta;
use App\Models\VentaItem;
use App\Models\Movimiento;
use App\Models\Turno;
use App\Traits\WithSwal;
use App\Traits\WithPermisos;
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

    // ─── Inicio de caja ───────────────────────────────────────────────────────
    public bool $mostrar_modal_caja = false;
    public string $monto_caja = '';
    public ?int $producto_pendiente_caja_id = null;
    public function mount()
    {
        $this->verificarAccesoPOS();
        $this->orden_productos = request()->cookie('pos_orden_productos', 'popularidad');
        $this->hay_fideo       = session('pos_hay_fideo', true);
        $this->iniciarVentaPendiente();
    }

    public function toggleHayFideo(): void
    {
        $this->hay_fideo = !$this->hay_fideo;
        session(['pos_hay_fideo' => $this->hay_fideo]);
    }

    // â”€â”€â”€ Crea (o recupera) la venta Pendiente del usuario actual â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    private function iniciarVentaPendiente(): void
    {
        // Eliminar ventas pendientes antiguas si ya son más de las 07:00
        if (now()->hour >= 7) {
            Venta::where('user_id', Auth::id())
                ->where('estado', 'Pendiente')
                ->where('created_at', '<', now()->copy()->setTime(7, 0, 0))
                ->delete();
        }

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
        $this->sincronizarCarritoDesdeDB($venta);
    }

    // â”€â”€â”€ Sincroniza el array $carrito desde los VentaItems de la BD â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    private function sincronizarCarritoDesdeDB(Venta $venta): void
    {
        $this->carrito = [];

        foreach ($venta->ventaItems as $item) {
            if (is_array($item->detalle)) {
                // Platos: un único VentaItem con detalle como JSON
                $key = "p_{$item->producto_id}";
                $acomp = [
                    'arroz' => (int) ($item->detalle['arroz'] ?? 0),
                    'fideo' => (int) ($item->detalle['fideo'] ?? 0),
                    'mixto' => (int) ($item->detalle['mixto'] ?? 0),
                ];
                $this->carrito[$key] = [
                    'tipo'        => 'plato',
                    'producto_id' => $item->producto_id,
                    'nombre'      => $item->producto->nombre ?? 'Producto',
                    'imagen'      => $item->producto->imagen ?? null,
                    'categoria'   => $item->producto->tipo ?? 'Platos',
                    'precio'      => (float) $item->precio,
                    'item_id'     => $item->id,
                    'acomp'       => $acomp,
                    'cantidad'    => array_sum($acomp),
                    'subtotal'    => array_sum($acomp) * (float) $item->precio,
                ];
            } else {
                // Simple: una entrada por producto
                $key = "s_{$item->producto_id}";
                $this->carrito[$key] = [
                    'tipo'        => 'simple',
                    'producto_id' => $item->producto_id,
                    'nombre'      => $item->producto->nombre ?? 'Producto',
                    'imagen'      => $item->producto->imagen ?? null,
                    'categoria'   => $item->producto->tipo ?? 'Refrescos',
                    'precio'      => (float) $item->precio,
                    'item_id'     => $item->id,
                    'cantidad'    => $item->cantidad,
                    'subtotal'    => (float) $item->subtotal,
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

        $qrImagen = \App\Models\User::find(Auth::id())
            ->tenants()
            ->wherePivot('tenant_id', \App\Helpers\TenantHelper::currentId())
            ->first()?->pivot?->qr_imagen;

        return view('livewire.pos', compact('productos', 'qrImagen'));
    }

    public function setOrdenProductos(string $orden): void
    {
        $this->orden_productos = $orden;
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

    // ─── Verifica si ya hay movimientos hoy después de las 02:00 ─────────────
    private function verificarMovimientosHoy(): bool
    {
        $turnoActivo = $this->getTurnoActivo();

        if (!$turnoActivo) {
            return true; // Sin turno activo, permitir flujo normal
        }

        $desde = now()->copy()->setTime(2, 0, 0);

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
                    'detalle'  => 'Retiro de saldo al iniciar caja',
                    'ingreso'  => 0,
                    'egreso'   => $saldoActual,
                    'saldo'    => 0,
                ]);
            }

            // 2. Ingresar el monto como cambio inicial de caja
            Movimiento::create([
                'turno_id' => $turnoActivo->id,
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
                    'tipo'        => 'plato',
                    'producto_id' => $producto->id,
                    'nombre'      => $producto->nombre,
                    'imagen'      => $producto->imagen ?? null,
                    'categoria'   => $producto->tipo ?? 'Platos',
                    'precio'      => (float) $producto->precio,
                    'item_id'     => $item->id,
                    'acomp'       => $acomp,
                    'cantidad'    => 1,
                    'subtotal'    => (float) $producto->precio,
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
                    'tipo'        => 'simple',
                    'producto_id' => $producto->id,
                    'nombre'      => $producto->nombre,
                    'imagen'      => $producto->imagen ?? null,
                    'categoria'   => $producto->tipo ?? 'Refrescos',
                    'precio'      => (float) $producto->precio,
                    'item_id'     => $item->id,
                    'cantidad'    => 1,
                    'subtotal'    => (float) $producto->precio,
                ];
            }
        }

        $this->calcularTotal();
        $this->actualizarTotalVenta();
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
    public function procesarVenta(string $tipoPago = 'efectivo', float $montoIngresado = 0): void
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

            $efectivo = $tipoPago === 'efectivo' ? $this->total : 0;
            $online   = $tipoPago === 'online'   ? $this->total : 0;

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

            $this->showSuccessNotification('Venta completada. Total: Bs. ' . number_format($this->total, 2));

            // Guardar el ID antes de reiniciar el estado
            $ventaCompletadaId = $venta->id;

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

            if ($tenant?->printer_auto_comanda ?? config('printer.auto_comanda')) {
                $built = $svc->buildComandaJob($ventaParaImprimir);
                if ($built['ok']) {
                    $this->dispatch('print-agent',
                        payload: array_merge(['printer' => $built['printer']], $built['job']),
                        ventaId: $ventaCompletadaId
                    );
                }
            }

            if ($tenant?->printer_auto_ticket ?? config('printer.auto_ticket')) {
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

    // â”€â”€â”€ Cancelar venta â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
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
        $query = Venta::whereDate('fecha_hora', now()->toDateString())
            ->whereNotIn('estado', ['Pendiente']);

        if ($turno) {
            $query->where('turno_id', $turno->id);
        }

        $ultimo = $query->orderBy('numero_venta', 'desc')->value('numero_venta');
        return $ultimo ? $ultimo + 1 : 1;
    }
}
