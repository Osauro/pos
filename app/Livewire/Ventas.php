<?php

namespace App\Livewire;

use App\Models\Venta;
use App\Models\User;
use App\Models\Turno;
use App\Traits\WithSwal;
use App\Traits\WithPermisos;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;
use Carbon\Carbon;

class Ventas extends Component
{
    use WithPagination, WithSwal, WithPermisos;

    public $turno_seleccionado = null;
    public $fecha_seleccionada = null;
    public $mostrarModalFiltro = false;
    public $mostrarModal = false;
    public $ventaSeleccionada = null;
    public $perPage = 10;

    // Propiedades deshabilitadas - no se usan kardex ni secuencia de pago
    // public $procesandoPago = false;
    // public $mostrarResumenEliminacion = false;
    // public $resumenEliminacion = [];
    // public $ventaAPagar = null;
    // public $mostrarErrorStock = false;
    // public $mostrarModalPago = false;

    public function mount()
    {
        $this->verificarAccesoVentas();
        $this->perPage = request()->cookie('paginateVentas', 10);

        // User: siempre semana actual, no puede cambiar
        if ($this->esUser()) {
            $inicioSemana = now()->startOfWeek(\Carbon\Carbon::MONDAY)->toDateString();
            $finSemana = now()->startOfWeek(\Carbon\Carbon::MONDAY)->addDays(6)->toDateString();

            $turnoActual = \App\Models\Turno::where('fecha_inicio', '<=', $finSemana)
                ->where('fecha_fin', '>=', $inicioSemana)
                ->first();

            $this->turno_seleccionado = $turnoActual ? $turnoActual->id : null;
        }
        // Admin: último turno por defecto con último día de venta
        elseif ($this->esAdmin()) {
            $ultimoTurno = \App\Models\Turno::where('encargado_id', auth()->id())
                ->orderBy('fecha_inicio', 'desc')
                ->first();

            if ($ultimoTurno) {
                $this->turno_seleccionado = $ultimoTurno->id;

                // Buscar la última fecha con ventas en este turno
                $ultimaVenta = Venta::where('turno_id', $ultimoTurno->id)
                    ->orderBy('fecha_hora', 'desc')
                    ->first();

                if ($ultimaVenta) {
                    $this->fecha_seleccionada = $ultimaVenta->fecha_hora->format('Y-m-d');
                }
            }
        }
        // Superadmin: turno actual si existe (de cualquier encargado)
        else {
            $inicioSemana = now()->startOfWeek(\Carbon\Carbon::MONDAY)->toDateString();
            $finSemana = now()->startOfWeek(\Carbon\Carbon::MONDAY)->addDays(6)->toDateString();

            $turnoActual = \App\Models\Turno::where('fecha_inicio', '<=', $finSemana)
                ->where('fecha_fin', '>=', $inicioSemana)
                ->first();

            if ($turnoActual) {
                $this->turno_seleccionado = $turnoActual->id;

                $ultimaVenta = Venta::where('turno_id', $turnoActual->id)
                    ->orderBy('fecha_hora', 'desc')
                    ->first();

                if ($ultimaVenta) {
                    $this->fecha_seleccionada = $ultimaVenta->fecha_hora->format('Y-m-d');
                }
            }
        }
    }

    public function render()
    {
        $query = Venta::with(['usuario', 'user', 'ventaItems.producto', 'turno'])
            ->whereIn('estado', ['Completo', 'Cancelado']);
        $turnos = collect();
        $mostrarFiltro = true;
        $puedeCrearVenta = false;

        // User: solo ventas de la semana actual, sin filtro
        if ($this->esUser()) {
            $inicioSemana = now()->startOfWeek(\Carbon\Carbon::MONDAY)->toDateString();
            $finSemana = now()->startOfWeek(\Carbon\Carbon::MONDAY)->addDays(6)->toDateString();

            // Buscar el turno activo de la semana actual
            $turnoActual = \App\Models\Turno::where('fecha_inicio', '<=', $finSemana)
                ->where('fecha_fin', '>=', $inicioSemana)
                ->first();

            if ($turnoActual) {
                $query->where('turno_id', $turnoActual->id);
            } else {
                // Si no hay turno activo, no mostrar nada
                $query->whereRaw('1 = 0');
            }

            $mostrarFiltro = false;
            $puedeCrearVenta = true;
        }
        // Admin: solo ventas de sus turnos
        elseif ($this->esAdmin()) {
            $misTurnos = \App\Models\Turno::where('encargado_id', auth()->id())
                ->orderBy('fecha_inicio', 'desc')
                ->get();

            $turnos = $misTurnos;

            // Si tiene fecha o turno seleccionado, filtrar por ese turno completo
            if ($this->turno_seleccionado) {
                // Verificar que el turno pertenezca al admin
                $turno = \App\Models\Turno::where('id', $this->turno_seleccionado)
                    ->where('encargado_id', auth()->id())
                    ->first();

                if ($turno) {
                    $query->where('turno_id', $turno->id);
                    if ($this->fecha_seleccionada) {
                        $query->whereDate('fecha_hora', $this->fecha_seleccionada);
                    }
                } else {
                    // Turno no válido, no mostrar nada
                    $query->whereRaw('1 = 0');
                }
            } else {
                // Si no hay turno seleccionado, mostrar ventas de todos sus turnos
                if ($misTurnos->isNotEmpty()) {
                    $query->whereIn('turno_id', $misTurnos->pluck('id'));
                } else {
                    // Si no tiene turnos, no mostrar nada
                    $query->whereRaw('1 = 0');
                }
            }

            // Admin puede crear venta solo si tiene turno activo
            $puedeCrearVenta = $this->tieneTurnoActivo();
        }
        // Superadmin: ve todos los turnos y todas las ventas
        else {
            // Filtrar por turno si está seleccionado
            if ($this->turno_seleccionado) {
                $turno = \App\Models\Turno::find($this->turno_seleccionado);
                if ($turno) {
                    $query->where('turno_id', $turno->id);
                    if ($this->fecha_seleccionada) {
                        $query->whereDate('fecha_hora', $this->fecha_seleccionada);
                    }
                }
            }

            // Obtener todos los turnos
            $turnos = \App\Models\Turno::with('encargado')
                ->orderBy('fecha_inicio', 'desc')
                ->get();

            $puedeCrearVenta = true;
        }

        $ventas = $query->orderBy('id', 'desc')->paginate($this->perPage);

        return view('livewire.ventas', compact('ventas', 'turnos', 'mostrarFiltro', 'puedeCrearVenta'))
            ->with('puedeEliminar', $this->puedeEliminar());
    }

    public function abrirModalFiltro()
    {
        // Solo admin y superadmin pueden usar el modal de filtro
        if (!$this->esUser()) {
            $this->mostrarModalFiltro = true;
            $this->dispatch('calendarioAbierto',
                fechasValidas: $this->obtenerFechasValidas(),
                rangoTurno: $this->obtenerRangoTurno(),
                todosTurnos: $this->obtenerTodosTurnos(),
                fechaSeleccionada: $this->fecha_seleccionada,
                turnoSeleccionadoId: $this->turno_seleccionado,
            );
        }
    }

    public function cerrarModalFiltro()
    {
        $this->mostrarModalFiltro = false;
    }

    public function limpiarFiltroFechas()
    {
        // Solo admin y superadmin pueden limpiar filtros
        if (!$this->esUser()) {
            $this->fecha_seleccionada = null;
            $this->turno_seleccionado = null;

            if ($this->esAdmin()) {
                $ultimoTurno = \App\Models\Turno::where('encargado_id', auth()->id())
                    ->orderBy('fecha_inicio', 'desc')
                    ->first();

                if ($ultimoTurno) {
                    $this->turno_seleccionado = $ultimoTurno->id;

                    $ultimaVenta = Venta::where('turno_id', $ultimoTurno->id)
                        ->orderBy('fecha_hora', 'desc')
                        ->first();

                    if ($ultimaVenta) {
                        $this->fecha_seleccionada = $ultimaVenta->fecha_hora->format('Y-m-d');
                    }
                }
            }

            $this->resetPage();
        }
    }

    public function seleccionarFecha($fecha)
    {
        $this->fecha_seleccionada = $fecha;

        // Encontrar el turno que corresponde a esta fecha
        if ($this->esAdmin()) {
            $turno = \App\Models\Turno::where('encargado_id', auth()->id())
                ->whereDate('fecha_inicio', '<=', $fecha)
                ->whereDate('fecha_fin', '>=', $fecha)
                ->first();

            if ($turno) {
                $this->turno_seleccionado = $turno->id;
            }
        }

        $this->cerrarModalFiltro();
        $this->resetPage();
    }

    public function obtenerFechasValidas()
    {
        if ($this->esAdmin()) {
            // Obtener todos los turnos del admin
            $misTurnos = \App\Models\Turno::where('encargado_id', auth()->id())->get();
            $fechas = [];

            foreach ($misTurnos as $turno) {
                $fechaInicio = Carbon::parse($turno->fecha_inicio);
                $fechaFin = Carbon::parse($turno->fecha_fin);

                // Incluir todas las fechas del turno (lunes a domingo)
                while ($fechaInicio->lte($fechaFin)) {
                    $fechas[] = $fechaInicio->format('Y-m-d');
                    $fechaInicio->addDay();
                }
            }

            return array_unique($fechas);
        }

        return [];
    }

    public function obtenerRangoTurno()
    {
        if ($this->turno_seleccionado) {
            $turno = Turno::find($this->turno_seleccionado);
            if ($turno) {
                return [
                    'inicio' => $turno->fecha_inicio,
                    'fin' => $turno->fecha_fin
                ];
            }
        }
        return null;
    }

    public function obtenerTodosTurnos()
    {
        if ($this->esAdmin()) {
            $misTurnos = \App\Models\Turno::where('encargado_id', auth()->id())
                ->orderBy('fecha_inicio', 'desc')
                ->get();

            $turnos = [];

            foreach ($misTurnos as $turno) {
                $turnos[] = [
                    'id' => $turno->id,
                    'inicio' => $turno->fecha_inicio,
                    'fin' => $turno->fecha_fin
                ];
            }

            return $turnos;
        }

        return [];
    }

    public function updatingTurnoSeleccionado()
    {
        // Solo admin y superadmin pueden cambiar turno
        if (!$this->esUser()) {
            $this->resetPage();
        }
    }

    public function crearVenta()
    {
        return redirect()->route('pos');
    }

    public function verDetalles($id)
    {
        $this->ventaSeleccionada = Venta::with(['usuario', 'user', 'ventaItems.producto'])->find($id);
        $this->mostrarModal = true;
    }

    public function cerrarModal()
    {
        $this->mostrarModal = false;
        $this->ventaSeleccionada = null;
    }

    public function cerrarResumen()
    {
        $this->cerrarModal();
    }

    public function reimprimirVenta($id)
    {
        $venta = Venta::with(['items.producto', 'turno.encargado', 'usuario'])->find($id);

        if (!$venta || $venta->estado !== 'Completo') {
            $this->showErrorNotification('Solo se pueden reimprimir ventas completadas.');
            return;
        }

        $tenant      = \App\Helpers\TenantHelper::current();
        $printerModo = $tenant?->printerModo() ?? 'browser';
        $svc         = app(\App\Services\EscposPrintService::class);

        if ($printerModo === 'escpos') {
            $printUrl = $svc->combinedUrl($venta);
            $this->dispatch('imprimir-venta',
                ventaId:  $venta->id,
                printUrl: $printUrl,
            );
        } elseif ($printerModo === 'network_ip') {
            $svc->printNetworkCombined($venta);
            $this->showSuccessNotification('Enviando a impresora de red...');
        } else {
            $this->dispatch('imprimir-venta',
                ventaId:     $venta->id,
                autoTicket:  true,
                autoComanda: true,
            );
        }
    }

    public function generarPDF($id)
    {
        // TODO: Implementar generación de PDF
        $this->showSuccessNotification('Función de PDF en desarrollo');
    }

    // Métodos de pago deshabilitados - no se usa secuencia de pago
    /*
    public function abrirModalPago($id)
    {
        // TODO: Implementar modal de pago
        $this->showSuccessNotification('Función de pago en desarrollo');
    }

    public function cerrarModalPago()
    {
        $this->procesandoPago = false;
    }
    */

    public function delete($id)
    {
        $this->deleteConfirmed($id);
    }

    #[On('deleteConfirmed')]
    public function deleteConfirmed($id)
    {
        $venta = Venta::find($id);
        if (!$venta || $venta->estado !== 'Completo') {
            $this->showErrorNotification('Solo se pueden cancelar ventas completadas.');
            return;
        }

        // Registrar egreso en movimientos
        if ($venta->turno_id && $venta->total > 0) {
            $saldo = \App\Models\Movimiento::where('turno_id', $venta->turno_id)
                ->orderBy('id', 'desc')
                ->value('saldo') ?? 0;
            \App\Models\Movimiento::create([
                'turno_id' => $venta->turno_id,
                'detalle'  => 'Anulación Venta #' . $venta->numero_venta,
                'ingreso'  => 0,
                'egreso'   => $venta->total,
                'saldo'    => $saldo - $venta->total,
            ]);
        }

        // Cancelar la venta sin borrar el registro ni los items
        $venta->update(['estado' => 'Cancelado']);

        $this->mostrarModal = false;
        $this->ventaSeleccionada = null;
        $this->showSuccessNotification('Venta #' . $venta->numero_venta . ' anulada');
    }
}
