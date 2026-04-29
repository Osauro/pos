<?php

namespace App\Livewire;

use App\Helpers\TenantHelper;
use App\Models\Movimiento;
use App\Models\Turno;
use App\Traits\WithSwal;
use App\Traits\WithPermisos;
use Carbon\Carbon;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

class Movimientos extends Component
{
    use WithPagination, WithSwal, WithPermisos;

    public $movimiento_id, $turno_id, $detalle, $monto = 0, $ingreso = 0, $egreso = 0, $saldo = 0;
    public $isOpen = false;
    public $tipo_movimiento = 'egreso'; // egreso o ingreso
    public bool $mostrarModalCambio = false;
    public string $montoCambio = '';
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
        'detalle'  => 'nullable|string|max:255',
        'monto'    => 'required|numeric|min:0.01',
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
        $query = Movimiento::with(['turno.encargado', 'usuario'])->orderBy('id', 'desc');
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
                    $query->where('turno_id', $this->turno_seleccionado);
                    $this->filtrarMovimientoPorDiaComercial($query, $this->fecha_seleccionada);
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
            ->with('puedeEliminar', $this->puedeEliminar())
            ->with('puedeCrearIngreso', $this->puedeCrearIngreso());
    }

    public function create()
    {
        $this->resetInputFields();

        // Operadores solo pueden crear egresos
        if (! $this->puedeCrearIngreso()) {
            $this->tipo_movimiento = 'egreso';
        }

        // Auto-detectar turno activo para el usuario actual
        if ($this->turno_seleccionado) {
            $this->turno_id = $this->turno_seleccionado;
        } else {
            // Admins buscan su propio turno; operadores usan cualquier turno activo del tenant
            $turnoActivo = $this->esUser()
                ? Turno::activo()->latest()->first()
                : Turno::activo()->where('encargado_id', auth()->id())->first();
            if ($turnoActivo) {
                $this->turno_id = $turnoActivo->id;
            }
        }

        // Sin turno: mostrar mensaje y no abrir modal
        if (!$this->turno_id) {
            if ($this->esUser()) {
                $this->swalWarning('Sin turno activo', 'No hay un turno activo. Solicita al administrador que cree un turno.');
            } else {
                $this->swalWarning('Sin turno activo', 'No tienes un turno activo. Ve a la sección de Turnos para crear uno.');
            }
            return;
        }

        // Primer movimiento del día → flujo inicio de caja
        if ($this->turno_id && $this->esPrimerMovimientoDelDia((int) $this->turno_id)) {
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
            $this->mostrarModalCambio = true;
            $this->dispatch('focusCambio');
            return;
        }

        $this->mostrarModal = true;
        $this->isOpen = true;
        $this->dispatch('focusMonto');
    }

    private function esPrimerMovimientoDelDia(int $turnoId): bool
    {
        $tenant = TenantHelper::current();
        // Inicio del día comercial actual
        $diaHoy = $tenant ? $tenant->businessDayFor(Carbon::now()) : Carbon::today();
        [$desde] = $tenant ? $tenant->businessDayRange($diaHoy) : [Carbon::today(), Carbon::today()->endOfDay()];

        return !Movimiento::where('turno_id', $turnoId)
            ->where('created_at', '>=', $desde)
            ->exists();
    }

    /**
     * Aplica filtro de día comercial sobre created_at del movimiento.
     */
    private function filtrarMovimientoPorDiaComercial($query, string $fecha): void
    {
        $tenant = TenantHelper::current();
        if (!$tenant || !$tenant->horario_inicio || !$tenant->horario_fin) {
            $query->whereDate('created_at', $fecha);
            return;
        }

        [$inicio, $fin] = $tenant->businessDayRange(Carbon::parse($fecha));
        $query->whereBetween('created_at', [$inicio, $fin]);
    }

    public function confirmarCambio(): void
    {
        $this->validate([
            'montoCambio' => 'required|numeric|min:0.01',
        ], [
            'montoCambio.required' => 'Ingresa el monto del cambio.',
            'montoCambio.numeric'  => 'El monto debe ser un número.',
            'montoCambio.min'      => 'El monto debe ser mayor a 0.',
        ]);

        if (!$this->turno_id) {
            $this->cancelarCambio();
            return;
        }

        DB::transaction(function () {
            $saldoActual = (float) (Movimiento::where('turno_id', $this->turno_id)
                ->orderBy('id', 'desc')
                ->value('saldo') ?? 0);

            // Egresar saldo anterior dejándolo en 0
            if ($saldoActual > 0) {
                Movimiento::create([
                    'turno_id' => $this->turno_id,
                    'user_id'  => auth()->id(),
                    'detalle'  => 'Retiro de saldo al iniciar caja',
                    'ingreso'  => 0,
                    'egreso'   => $saldoActual,
                    'saldo'    => 0,
                ]);
            }

            // Ingresar el cambio inicial
            Movimiento::create([
                'turno_id' => $this->turno_id,
                'user_id'  => auth()->id(),
                'detalle'  => 'Inicio de caja',
                'ingreso'  => (float) $this->montoCambio,
                'egreso'   => 0,
                'saldo'    => (float) $this->montoCambio,
            ]);
        });

        $this->showSuccessNotification('Inicio de caja registrado');
        $this->cancelarCambio();

        // Actualizar filtro al día de hoy para que la lista refleje el nuevo día
        $this->fecha_seleccionada = now()->toDateString();
        $this->resetPage();
    }

    public function cancelarCambio(): void
    {
        $this->mostrarModalCambio = false;
        $this->montoCambio = '';
        $this->resetValidation('montoCambio');
    }

    public function save()
    {
        if ($this->movimiento_id) {
            $this->update();
        } else {
            $this->store();
        }
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
        $this->validate([
            'turno_id' => 'required|exists:turnos,id',
            'monto'    => 'required|numeric|min:0.01',
        ]);

        // Operadores solo pueden registrar egresos
        if ($this->tipo_movimiento === 'ingreso' && ! $this->puedeCrearIngreso()) {
            $this->addError('monto', 'Solo el administrador puede registrar ingresos.');
            return;
        }

        // Si no se escribió detalle, usar el tipo de operación como etiqueta
        $detalle = trim($this->detalle) ?: ucfirst($this->tipo_movimiento);

        $ingreso = $this->tipo_movimiento === 'ingreso' ? $this->monto : 0;
        $egreso  = $this->tipo_movimiento === 'egreso'  ? $this->monto : 0;

        $ultimoMovimiento = Movimiento::where('turno_id', $this->turno_id)
            ->orderBy('id', 'desc')
            ->first();

        $saldoAnterior = $ultimoMovimiento ? $ultimoMovimiento->saldo : 0;

        // Validar que haya saldo suficiente para egresos
        if ($this->tipo_movimiento === 'egreso' && $saldoAnterior <= 0) {
            $this->swalWarning('Sin saldo en caja', 'No hay saldo disponible en caja para registrar un egreso.');
            return;
        }

        if ($this->tipo_movimiento === 'egreso' && $egreso > $saldoAnterior) {
            $this->swalWarning('Saldo insuficiente', 'El monto del egreso (Bs. ' . number_format($egreso, 2) . ') supera el saldo en caja (Bs. ' . number_format($saldoAnterior, 2) . ').');
            return;
        }

        $nuevoSaldo    = $saldoAnterior + $ingreso - $egreso;

        Movimiento::create([
            'turno_id' => $this->turno_id,
            'user_id'  => auth()->id(),
            'detalle'  => $detalle,
            'ingreso'  => $ingreso,
            'egreso'   => $egreso,
            'saldo'    => $nuevoSaldo,
        ]);

        $this->showSuccessNotification('Movimiento registrado exitosamente');
        $this->closeModal();
    }

    public function edit($id)
    {
        $movimiento = Movimiento::findOrFail($id);
        $this->movimiento_id = $id;
        $this->turno_id = $movimiento->turno_id;
        $this->detalle = $movimiento->detalle;
        $this->ingreso = $movimiento->ingreso;
        $this->egreso  = $movimiento->egreso;

        if ($movimiento->ingreso > 0) {
            $this->tipo_movimiento = 'ingreso';
            $this->monto = $movimiento->ingreso;
        } else {
            $this->tipo_movimiento = 'egreso';
            $this->monto = $movimiento->egreso;
        }

        $this->isOpen = true;
        $this->mostrarModal = true;
    }

    public function update()
    {
        $this->validate([
            'turno_id' => 'required|exists:turnos,id',
            'monto'    => 'required|numeric|min:0.01',
        ]);

        $detalle = trim($this->detalle) ?: ucfirst($this->tipo_movimiento);
        $ingreso = $this->tipo_movimiento === 'ingreso' ? $this->monto : 0;
        $egreso  = $this->tipo_movimiento === 'egreso'  ? $this->monto : 0;

        $movimiento = Movimiento::find($this->movimiento_id);
        $movimiento->turno_id = $this->turno_id;
        $movimiento->detalle  = $detalle;
        $movimiento->ingreso  = $ingreso;
        $movimiento->egreso   = $egreso;

        // Recalcular saldo
        $ultimoMovimiento = Movimiento::where('turno_id', $this->turno_id)
            ->where('id', '<', $this->movimiento_id)
            ->orderBy('id', 'desc')
            ->first();

        $saldoAnterior = $ultimoMovimiento ? $ultimoMovimiento->saldo : 0;
        $movimiento->saldo = $saldoAnterior + $ingreso - $egreso;

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
        $this->monto = 0;
        $this->ingreso = 0;
        $this->egreso = 0;
        $this->saldo = 0;
        $this->tipo_movimiento = 'egreso';
        $this->resetValidation();
    }
}
