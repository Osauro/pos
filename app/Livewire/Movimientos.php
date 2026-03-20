<?php

namespace App\Livewire;

use App\Models\Movimiento;
use App\Models\Turno;
use App\Traits\WithSwal;
use App\Traits\WithPermisos;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class Movimientos extends Component
{
    use WithPagination, WithSwal, WithPermisos;

    public $movimiento_id, $turno_id, $detalle, $ingreso = 0, $egreso = 0, $saldo = 0;
    public $isOpen = false;
    public $tipo_movimiento = 'egreso'; // egreso o ingreso
    public $perPage = 10;
    public $fecha_inicio = null;
    public $fecha_fin = null;
    public $fecha_seleccionada = null;
    public $turno_seleccionado = null;
    public $isOpenFiltro = false;
    public $mostrarModal = false;
    public $mostrarModalFiltro = false;

    protected $rules = [
        'turno_id' => 'required|exists:turnos,id',
        'detalle' => 'required|string|max:255',
        'ingreso' => 'nullable|numeric|min:0',
        'egreso' => 'nullable|numeric|min:0'
    ];

    public function mount()
    {
        $this->verificarAccesoMovimientos();
        $this->perPage = request()->cookie('paginateMovimientos', 10);

        // Admin: último turno + último día con movimientos por defecto
        if ($this->esAdmin()) {
            $ultimoTurno = \App\Models\Turno::where('encargado_id', auth()->id())
                ->orderBy('fecha_inicio', 'desc')
                ->first();

            if ($ultimoTurno) {
                $this->turno_seleccionado = $ultimoTurno->id;

                // Último día del turno que tenga movimientos
                $ultimaFecha = Movimiento::where('turno_id', $ultimoTurno->id)
                    ->orderBy('id', 'desc')
                    ->value('created_at');

                if ($ultimaFecha) {
                    $this->fecha_seleccionada = \Carbon\Carbon::parse($ultimaFecha)->toDateString();
                }
            }
        }
    }

    public function render()
    {
        $query = Movimiento::with('turno.encargado')->orderBy('id', 'desc');
        $turnos = collect();

        // User: solo movimientos de la semana actual
        if ($this->esUser()) {
            $inicioSemana = now()->startOfWeek(\Carbon\Carbon::MONDAY)->toDateString();
            $finSemana = now()->startOfWeek(\Carbon\Carbon::MONDAY)->addDays(6)->toDateString();

            // Buscar el turno activo de la semana actual
            $turnoActual = Turno::where('fecha_inicio', '<=', $finSemana)
                ->where('fecha_fin', '>=', $inicioSemana)
                ->first();

            if ($turnoActual) {
                $query->where('turno_id', $turnoActual->id);
            } else {
                // Si no hay turno activo, no mostrar nada
                $query->whereRaw('1 = 0');
            }
        }
        // Admin: solo movimientos de sus turnos
        elseif ($this->esAdmin()) {
            $misTurnos = Turno::where('encargado_id', auth()->id())
                ->orderBy('fecha_inicio', 'desc')
                ->get();

            if ($misTurnos->isNotEmpty()) {
                // Si tiene fecha seleccionada, filtrar por día específico del turno
                if ($this->fecha_seleccionada && $this->turno_seleccionado) {
                    $query->where('turno_id', $this->turno_seleccionado)
                          ->whereDate('created_at', $this->fecha_seleccionada);
                } elseif ($this->turno_seleccionado) {
                    $query->where('turno_id', $this->turno_seleccionado);
                } else {
                    $query->whereIn('turno_id', $misTurnos->pluck('id'));
                }

                $turnos = $misTurnos;
            } else {
                // Si no tiene turnos, no mostrar nada
                $query->whereRaw('1 = 0');
            }

            // Aplicar filtro de fechas si existen (filtro antiguo)
            if ($this->fecha_inicio && $this->fecha_fin) {
                $query->whereHas('turno', function ($q) {
                    $q->whereBetween('fecha_inicio', [$this->fecha_inicio, $this->fecha_fin])
                      ->orWhereBetween('fecha_fin', [$this->fecha_inicio, $this->fecha_fin]);
                });
            }
        }
        // Superadmin: ve todos los movimientos
        else {
            // Aplicar filtro de fechas si existen
            if ($this->fecha_inicio && $this->fecha_fin) {
                $query->whereHas('turno', function ($q) {
                    $q->whereBetween('fecha_inicio', [$this->fecha_inicio, $this->fecha_fin])
                      ->orWhereBetween('fecha_fin', [$this->fecha_inicio, $this->fecha_fin]);
                });
            }

            $turnos = Turno::with('encargado')->orderBy('id', 'desc')->get();
        }

        $movimientos = $query->paginate($this->perPage);

        // Calcular totales SIEMPRE por turno completo, sin filtro de fecha
        $queryTotales = Movimiento::query();

        if ($this->esUser()) {
            $inicioSemana = now()->startOfWeek(\Carbon\Carbon::MONDAY)->toDateString();
            $finSemana = now()->startOfWeek(\Carbon\Carbon::MONDAY)->addDays(6)->toDateString();

            $turnoActual = Turno::where('fecha_inicio', '<=', $finSemana)
                ->where('fecha_fin', '>=', $inicioSemana)
                ->first();

            if ($turnoActual) {
                $queryTotales->where('turno_id', $turnoActual->id);
            } else {
                $queryTotales->whereRaw('1 = 0');
            }
        } elseif ($this->esAdmin()) {
            $misTurnos = Turno::where('encargado_id', auth()->id())
                ->orderBy('fecha_inicio', 'desc')
                ->get();

            if ($misTurnos->isNotEmpty()) {
                // Siempre filtrar solo por turno, nunca por fecha específica
                if ($this->turno_seleccionado) {
                    $queryTotales->where('turno_id', $this->turno_seleccionado);
                } else {
                    $queryTotales->whereIn('turno_id', $misTurnos->pluck('id'));
                }
            } else {
                $queryTotales->whereRaw('1 = 0');
            }
        }
        // Superadmin: totales globales sin filtro

        $totalIngresos = $queryTotales->sum('ingreso');
        $totalEgresos = $queryTotales->sum('egreso');
        $saldoActual = $totalIngresos - $totalEgresos;

        $mostrarFiltro = !$this->esUser();

        return view('livewire.movimientos', compact('movimientos', 'turnos', 'totalIngresos', 'totalEgresos', 'saldoActual', 'mostrarFiltro'))
            ->with('puedeEliminar', $this->puedeEliminar());
    }

    public function create()
    {
        $this->resetInputFields();
        $this->mostrarModal = true;
        $this->isOpen = true;
    }

    public function abrirModalFiltro()
    {
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
        $this->isOpenFiltro = false;
    }

    public function aplicarFiltroFechas()
    {
        $this->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
        ]);

        $this->cerrarModalFiltro();
        $this->resetPage();
    }

    public function limpiarFiltroFechas()
    {
        $this->fecha_inicio = null;
        $this->fecha_fin = null;
        $this->fecha_seleccionada = null;
        $this->turno_seleccionado = null;

        if ($this->esAdmin()) {
            $ultimoTurno = Turno::where('encargado_id', auth()->id())
                ->orderBy('fecha_inicio', 'desc')
                ->first();

            if ($ultimoTurno) {
                $this->turno_seleccionado = $ultimoTurno->id;

                $ultimaFecha = Movimiento::where('turno_id', $ultimoTurno->id)
                    ->orderBy('id', 'desc')
                    ->value('created_at');

                if ($ultimaFecha) {
                    $this->fecha_seleccionada = \Carbon\Carbon::parse($ultimaFecha)->toDateString();
                }
            }
        }

        $this->resetPage();
    }

    public function seleccionarFecha($fecha)
    {
        $this->fecha_seleccionada = $fecha;
        $this->resetPage();

        // Encontrar el turno que corresponde a esta fecha
        if ($this->esAdmin()) {
            $turno = Turno::where('encargado_id', auth()->id())
                ->whereDate('fecha_inicio', '<=', $fecha)
                ->whereDate('fecha_fin', '>=', $fecha)
                ->first();

            if ($turno) {
                $this->turno_seleccionado = $turno->id;
            }
        }

        $this->cerrarModalFiltro();
    }

    public function obtenerFechasValidas()
    {
        if ($this->esAdmin()) {
            $misTurnos = Turno::where('encargado_id', auth()->id())->get();
            $fechas = [];

            foreach ($misTurnos as $turno) {
                $fechaInicio = \Carbon\Carbon::parse($turno->fecha_inicio);
                $fechaFin = \Carbon\Carbon::parse($turno->fecha_fin);

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
                    'fin'    => $turno->fecha_fin,
                ];
            }
        }
        return null;
    }

    public function obtenerTodosTurnos()
    {
        if ($this->esAdmin()) {
            return Turno::where('encargado_id', auth()->id())
                ->orderBy('fecha_inicio', 'desc')
                ->get()
                ->map(fn($t) => ['id' => $t->id, 'inicio' => $t->fecha_inicio, 'fin' => $t->fecha_fin])
                ->toArray();
        }
        return [];
    }

    public function store()
    {
        $this->validate();

        // Calcular el saldo basado en el último movimiento del turno
        $ultimoMovimiento = Movimiento::where('turno_id', $this->turno_id)
            ->orderBy('id', 'desc')
            ->first();

        $saldoAnterior = $ultimoMovimiento ? $ultimoMovimiento->saldo : 0;
        $nuevoSaldo = $saldoAnterior + ($this->ingreso ?? 0) - ($this->egreso ?? 0);

        Movimiento::create([
            'turno_id' => $this->turno_id,
            'detalle' => $this->detalle,
            'ingreso' => $this->ingreso ?? 0,
            'egreso' => $this->egreso ?? 0,
            'saldo' => $nuevoSaldo
        ]);

        $this->showSuccessNotification('Movimiento creado exitosamente');
        $this->closeModal();
    }

    public function edit($id)
    {
        $movimiento = Movimiento::findOrFail($id);
        $this->movimiento_id = $id;
        $this->turno_id = $movimiento->turno_id;
        $this->detalle = $movimiento->detalle;
        $this->ingreso = $movimiento->ingreso;
        $this->egreso = $movimiento->egreso;

        $this->isOpen = true;
        $this->mostrarModal = true;
    }

    public function update()
    {
        $this->validate();

        $movimiento = Movimiento::find($this->movimiento_id);
        $movimiento->turno_id = $this->turno_id;
        $movimiento->detalle = $this->detalle;
        $movimiento->ingreso = $this->ingreso ?? 0;
        $movimiento->egreso = $this->egreso ?? 0;

        // Recalcular saldo
        $ultimoMovimiento = Movimiento::where('turno_id', $this->turno_id)
            ->where('id', '<', $this->movimiento_id)
            ->orderBy('id', 'desc')
            ->first();

        $saldoAnterior = $ultimoMovimiento ? $ultimoMovimiento->saldo : 0;
        $movimiento->saldo = $saldoAnterior + $movimiento->ingreso - $movimiento->egreso;

        $movimiento->save();

        $this->showSuccessNotification('Movimiento actualizado exitosamente');
        $this->closeModal();
    }

    public function delete($id)
    {
        $this->confirmDelete($id, 'deleteConfirmed');
    }

    #[On('deleteConfirmed')]
    public function deleteConfirmed($id)
    {
        Movimiento::find($id)->delete();
        $this->showSuccessNotification('Movimiento eliminado exitosamente');
    }

    public function closeModal()
    {
        $this->isOpen = false;
        $this->mostrarModal = false;
        $this->resetInputFields();
    }

    private function resetInputFields()
    {
        $this->movimiento_id = null;
        $this->turno_id = '';
        $this->detalle = '';
        $this->ingreso = 0;
        $this->egreso = 0;
        $this->saldo = 0;
        $this->tipo_movimiento = 'egreso';
        $this->resetValidation();
    }
}
