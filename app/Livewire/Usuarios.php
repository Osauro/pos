<?php

namespace App\Livewire;

use App\Models\Usuario;
use App\Traits\WithSwal;
use App\Traits\WithPermisos;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Hash;

class Usuarios extends Component
{
    use WithPagination, WithSwal, WithPermisos;

    public $usuario_id, $nombre, $celular, $pin, $tipo = 'user';
    public $isOpen = false;
    public $perPage = 10;

    protected $rules = [
        'nombre' => 'required|string|max:255',
        'celular' => 'required|digits:8',
        'tipo' => 'required|in:admin,user'
    ];

    private function generarPin(): string
    {
        return str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT);
    }

    public function mount()
    {
        $this->verificarAccesoUsuarios();
        $this->perPage = request()->cookie('paginateUsuarios', 10);
    }

    public function render()
    {
        $usuarios = Usuario::orderBy('id', 'desc')
            ->paginate($this->perPage);

        return view('livewire.usuarios', compact('usuarios'))
            ->with('puedeEliminar', $this->puedeEliminar());
    }

    public function create()
    {
        $this->resetInputFields();
        $this->isOpen = true;
    }

    public function store()
    {
        $this->validate();

        $pin = $this->generarPin();

        $usuario = Usuario::create([
            'nombre' => $this->nombre,
            'celular' => $this->celular,
            'pin' => Hash::make($pin),
            'tipo' => $this->tipo
        ]);

        $this->closeModal();
        $this->dispatch('swal:pin', nombre: $usuario->nombre, pin: $pin);
    }

    public function edit($id)
    {
        $usuario = Usuario::findOrFail($id);
        $this->usuario_id = $id;
        $this->nombre = $usuario->nombre;
        $this->celular = $usuario->celular;
        $this->tipo = $usuario->tipo;
        $this->pin = '';

        $this->isOpen = true;
    }

    public function update()
    {
        $this->validate();

        $pin = $this->generarPin();

        $usuario = Usuario::find($this->usuario_id);
        $usuario->nombre = $this->nombre;
        $usuario->celular = $this->celular;
        $usuario->tipo = $this->tipo;
        $usuario->pin = Hash::make($pin);
        $usuario->save();

        $this->closeModal();
        $this->dispatch('swal:pin', nombre: $usuario->nombre, pin: $pin);
    }

    public function resetPin($id)
    {
        $usuario = Usuario::findOrFail($id);
        $pin = $this->generarPin();
        $usuario->pin = Hash::make($pin);
        $usuario->save();
        $this->dispatch('swal:pin', nombre: $usuario->nombre, pin: $pin);
    }

    public function delete($id)
    {
        $this->confirmDelete($id, 'deleteConfirmed');
    }

    #[On('deleteConfirmed')]
    public function deleteConfirmed($id)
    {
        Usuario::find($id)->delete();
        $this->showSuccessNotification('Usuario eliminado exitosamente');
    }

    public function closeModal()
    {
        $this->isOpen = false;
        $this->resetInputFields();
    }

    private function resetInputFields()
    {
        $this->usuario_id = null;
        $this->nombre = '';
        $this->celular = '';
        $this->pin = '';
        $this->tipo = 'user';
        $this->resetValidation();
    }
}
