<?php

namespace App\Livewire;

use App\Models\Turno;
use App\Models\Usuario;
use App\Traits\WithSwal;
use App\Traits\WithPermisos;
use Livewire\Component;
use Livewire\WithPagination;
use Carbon\Carbon;

class Turnos extends Component
{
    use WithPagination, WithSwal, WithPermisos;

    public $encargado_id, $fecha_inicio, $fecha_fin;
    public $isOpen = false;
    public $year;
    public $perPage = 10;

    protected $rules = [
        'encargado_id' => 'required|exists:usuarios,id',
        'fecha_inicio' => 'required|date',
        'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
    ];

    public function mount()
    {
        $this->verificarAccesoTurnos();
        $this->perPage = request()->cookie('paginateTurnos', 10);
        $this->year = date('Y');
    }

    public function updatingYear()
    {
        $this->resetPage();
    }

    public function render()
    {
        $turnos = Turno::with('encargado')
            ->whereYear('fecha_inicio', $this->year)
            ->orderBy('fecha_inicio', 'desc')
            ->paginate($this->perPage);

        $usuarios = Usuario::where('tipo', 'admin')->get();

        return view('livewire.turnos', compact('turnos', 'usuarios'));
    }

    public function create()
    {
        $this->resetInputFields();
        $this->fecha_inicio = Carbon::now()->startOfWeek(Carbon::MONDAY)->format('Y-m-d');
        $this->fecha_fin    = Carbon::now()->startOfWeek(Carbon::MONDAY)->addDays(6)->format('Y-m-d');
        $this->isOpen = true;
    }

    public function quickStore($encargadoId)
    {
        $inicio = Carbon::now()->startOfWeek(Carbon::MONDAY)->format('Y-m-d');
        $fin    = Carbon::now()->startOfWeek(Carbon::MONDAY)->addDays(6)->format('Y-m-d');

        if (Turno::rangoOcupado($inicio, $fin)) {
            $this->showErrorNotification('Ya existe un turno en ese rango de fechas.');
            return;
        }

        // Finalizar turno anterior del mismo encargado
        Turno::where('encargado_id', $encargadoId)
            ->where('estado', 'activo')
            ->update(['estado' => 'finalizado']);

        Turno::create([
            'encargado_id' => $encargadoId,
            'fecha_inicio' => $inicio,
            'fecha_fin'    => $fin,
            'estado'       => 'activo',
        ]);

        $this->showSuccessNotification('Turno creado exitosamente');
        $this->closeModal();
    }

    public function closeModal()
    {
        $this->isOpen = false;
        $this->resetInputFields();
    }

    private function resetInputFields()
    {
        $this->encargado_id = '';
        $this->fecha_inicio = '';
        $this->fecha_fin    = '';
    }
}
